<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 02.01.15 - 22:37
 */

namespace SqlConnector\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Ginger\Message\AbstractWorkflowMessageHandler;
use Ginger\Message\GingerMessage;
use Ginger\Message\LogMessage;
use Ginger\Message\MessageNameUtils;
use Ginger\Message\WorkflowMessage;
use Ginger\Message\WorkflowMessageHandler;
use Ginger\Type\Description\Description;
use Ginger\Type\Description\NativeType;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Zend\Stdlib\ErrorHandler;
use Zend\XmlRpc\Value\AbstractCollection;

/**
 * Class DoctrineTableGateway
 *
 * @package SqlConnector\src\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class DoctrineTableGateway extends AbstractWorkflowMessageHandler
{
    /**
     * Key in collect-data single result metadata. If set to TRUE the TableGateway uses the identifier name of the ginger type to find a result
     */
    const META_IDENTIFIER = 'identifier';

    /**
     * Key in collect-data metadata. Key-Value-Pairs of filters. Multiple filters are combined to a And-Filer.
     * If key is a table column and value a scalar value a key = value (aka column = value) filter is applied.
     * If value is an array it should contain a operand and value definition and optional a column definition if key is not the column name.
     * The latter is useful when specifying a between filter:
     *
     * filter = [
     *   'age_min' => [
     *     'column' => 'age',
     *     'operand' => '>=',
     *     'value' => 21
     *   ],
     *   'age_max' => [
     *     'column' => 'age',
     *     'operand' => '<=',
     *     'value' => 120
     *   ]
     * [
     */
    const META_FILTER = "filter";

    /**
     * Filter keys. See example above
     */
    const FILTER_COLUMN = "column";
    const FILTER_OPERAND = "operand";
    const FILTER_VALUE  = "value";

    /**
     * Available operands for a filter.
     */
    const OPERAND_EQ      = "=";
    const OPERAND_LT      = "<";
    const OPERAND_LTE     = "<=";
    const OPERAND_GTE     = ">=";
    const OPERAND_GT      = ">";
    const OPERAND_LIKE    = "like";
    const OPERAND_LIKE_CI = "ilike";

    /**
     * If filters are not enough to fetch the right data you can also provide a link to a file that provides a callable which modifies the query.
     * The callable should take two arguments the Doctrine\DBAL\Query\QueryBuilder and the metadata array.
     * It should directly operate on the query builder.
     *
     * Note: The table of the requested ginger type is aliased as "main"
     * Note: Don't set limit, offset or order_by. Use metadata for them, because the TableGateway performs also a count query without offset, limit and order_by
     *       but with all filters applied.
     *
     * example query builder script:
     * <code>
     * //Return a callable from the script
     * return function(\Doctrine\DBAL\Query\QueryBuilder $query, array $metadata) {
     *   $query->orWhere($query->exp()->eq('vip', 1));
     * };
     * </code>
     */
    const META_QUERY_BUILDER_SCRIPT = "query_builder_script";

    /**
     * Set the query offset.
     */
    const META_OFFSET = "offset";

    /**
     * Set the query limit
     */
    const META_LIMIT = "limit";

    /**
     * Set order_by like you would do in a SQL query.
     *
     * example: "age DESC,name ASC"
     */
    const META_ORDER_BY = "order_by";

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $table;

    /**
     * @param Connection $connection
     * @param $table
     */
    public function __construct(Connection $connection, $table)
    {
        $this->connection = $connection;
        $this->table      = $table;
    }

    /**
     * If workflow message handler receives a collect-data message it forwards the message to this
     * method and uses the returned GingerMessage as response
     *
     * @param WorkflowMessage $workflowMessage
     * @return GingerMessage
     */
    protected function handleCollectData(WorkflowMessage $workflowMessage)
    {
        try {
            return $this->collectData($workflowMessage);
        } catch (\Exception $ex) {
            ErrorHandler::stop();
            return LogMessage::logException($ex, $workflowMessage->processTaskListPosition());
        }
    }

    /**
     * If workflow message handler receives a process-data message it forwards the message to this
     * method and uses the returned GingerMessage as response
     *
     * @param WorkflowMessage $workflowMessage
     * @return GingerMessage
     */
    protected function handleProcessData(WorkflowMessage $workflowMessage)
    {
        try {
            //@TODO: Add handling of process-data
            throw new \BadMethodCallException(__METHOD__ . " not supported by " . __CLASS__);
        } catch (\Exception $ex) {
            ErrorHandler::stop();
            return LogMessage::logException($ex, $workflowMessage->processTaskListPosition());
        }
    }

    /**
     * @param WorkflowMessage $workflowMessage
     */
    private function collectData(WorkflowMessage $workflowMessage)
    {
        $gingerType = $workflowMessage->payload()->getTypeClass();

        /** @var $desc Description */
        $desc = $gingerType::buildDescription();

        switch ($desc->nativeType()) {
            case NativeType::COLLECTION:
                return $this->collectResultSet($workflowMessage);
                break;
            case NativeType::DICTIONARY:
                return $this->collectSingleResult($workflowMessage);
                break;
            default:
                return LogMessage::logUnsupportedMessageReceived($workflowMessage, 'sqlconnector-' . $this->table);
        }
    }

    /**
     * @param WorkflowMessage $workflowMessage
     * @return WorkflowMessage
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    private function collectSingleResult(WorkflowMessage $workflowMessage)
    {
        $itemType = $workflowMessage->payload()->getTypeClass();

        $metadata = $workflowMessage->metadata();

        if (! method_exists($itemType, 'fromDatabaseRow')) throw new \InvalidArgumentException(sprintf("Item type %s does not provide a static fromDatabaseRow factory method", $itemType));
        if (! method_exists($itemType, 'toDbColumnName')) throw new \InvalidArgumentException(sprintf("Item type %s does not provide a static toDbColumnName method", $itemType));

        $query = $this->buildQueryFromMetadata($itemType, $metadata);

        if (isset($metadata[self::META_IDENTIFIER])) {
            if (! $itemType::prototype()->typeDescription()->hasIdentifier()) throw new \InvalidArgumentException(sprintf("Item type %s has no identifier", $itemType));

            $identifierName = $itemType::prototype()->typeDescription()->identifierName();

            $this->addFilter($query, $itemType::toDbColumnName($identifierName), '=', $metadata['identifier'], 1000);
        }

        $itemData = $query->execute()->fetch();

        if (is_null($itemData)) {
            throw new \RuntimeException(sprintf("No %s found using metadata %s", $itemType, json_encode($workflowMessage->metadata())));
        }

        return $workflowMessage->answerWith($itemType::fromDatabaseRow($itemData));
    }

    private function collectResultSet(WorkflowMessage $workflowMessage)
    {
        /** @var $collectionType AbstractCollection */
        $collectionType = $workflowMessage->payload()->getTypeClass();

        $itemType = $collectionType::prototype()->typeProperties()['item']->typePrototype()->of();

        if (! method_exists($itemType, 'fromDatabaseRow')) throw new \InvalidArgumentException(sprintf("Item type %s does not provide a static fromDatabaseRow factory method", $itemType));
        if (! method_exists($itemType, 'toDbColumnName')) throw new \InvalidArgumentException(sprintf("Item type %s does not provide a static toDbColumnName method", $itemType));

        $count = $this->countRows($itemType, $workflowMessage->metadata());

        $query = $this->buildQueryFromMetadata($itemType, $workflowMessage->metadata());

        $resultSet = $query->execute()->fetchAll();


        $items = [];

        foreach ( $resultSet as $itemData) {
            $items[] = $itemType::fromDatabaseRow($itemData);
        }

        $collection = $collectionType::fromNativeValue($items);

        return $workflowMessage->answerWith($collection, ['total_items' => $count]);
    }

    /**
     * @param string $itemType
     * @param array $metadata
     * @return int
     */
    private function countRows($itemType, array $metadata)
    {
        $query = $this->buildQueryFromMetadata($itemType, $metadata, true);

        return $query->execute()->fetchColumn();
    }

    /**
     * @param string $itemType
     * @param array $metadata
     * @param bool $countMode
     * @throws \InvalidArgumentException
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function buildQueryFromMetadata($itemType, array $metadata, $countMode = false)
    {
        ErrorHandler::start();

        $query = $this->connection->createQueryBuilder();

        if ($countMode) {
            $query->select('COUNT(*)');
        } else {
            $query->select('*');
        }

        $query->from($this->table, 'main');

        if (isset($metadata[self::META_FILTER]) && is_array($metadata[self::META_FILTER])) {
            $filterCount = 0;

            foreach ($metadata[self::META_FILTER] as $column => $value) {
                $filterCount++;

                if (is_array($value)) {
                    if (! array_key_exists(self::FILTER_OPERAND, $value)) throw new \InvalidArgumentException(sprintf('Missing operand in filter array for column %s', $column));
                    if (! array_key_exists(self::FILTER_VALUE, $value)) throw new \InvalidArgumentException(sprintf('Missing value in filter array for column %s', $column));

                    if (isset($value[self::FILTER_COLUMN])) {
                        $column = $value[self::FILTER_COLUMN];
                    }

                    $column = $itemType::toDbColumnName($column);

                    $this->addFilter($query, $column, $value[self::FILTER_OPERAND], $value[self::FILTER_VALUE], $filterCount);
                } else {
                    $this->addFilter($query, $column, '=', $value, $filterCount);
                }
            }
        }

        if (isset($metadata[self::META_QUERY_BUILDER_SCRIPT]) && file_exists($metadata[self::META_QUERY_BUILDER_SCRIPT])) {
            $queryBuilderFunc = include($metadata[self::META_QUERY_BUILDER_SCRIPT]);
            if (is_callable($queryBuilderFunc)) $queryBuilderFunc($query, $metadata);
        }

        if (! $countMode) {
            if (isset($metadata[self::META_OFFSET])) {
                $query->setFirstResult($metadata[self::META_OFFSET]);
            }

            if (isset($metadata[self::META_LIMIT])) {
                $query->setMaxResults($metadata[self::META_LIMIT]);
            }

            if (isset($metadata[self::META_ORDER_BY])) {
                $orderByArr = explode(",", $metadata[self::META_ORDER_BY]);

                foreach ($orderByArr as $orderBy) {
                    $orderBy = trim($orderBy);

                    $orderByParts = explode(" ", $orderBy);

                    $order = isset($orderByParts[1])? $orderByParts[1] : 'ASC';

                    $query->addOrderBy($orderByParts[0], $order);
                }
            }
        }

        ErrorHandler::stop(true);

        return $query;
    }

    /**
     * @param QueryBuilder $query
     * @param string $column
     * @param string $operand
     * @param mixed $value
     * @param int $filterCount
     */
    private function addFilter(QueryBuilder $query, $column, $operand, $value, $filterCount)
    {
        switch ($operand) {
            case self::OPERAND_EQ:
                $query->andWhere($query->expr()->eq($column, ':__filter' . $filterCount));
                break;
            case self::OPERAND_LT:
                $query->andWhere($query->expr()->lt($column, ':__filter' . $filterCount));
                break;
            case self::OPERAND_LTE:
                $query->andWhere($query->expr()->lte($column, ':__filter' . $filterCount));
                break;
            case self::OPERAND_GT:
                $query->andWhere($query->expr()->gt($column, ':__filter' . $filterCount));
                break;
            case self::OPERAND_GTE:
                $query->andWhere($query->expr()->gte($column, ':__filter' . $filterCount));
                break;
            case self::OPERAND_LIKE:
                $query->andWhere($query->expr()->like($column, ':__filter' . $filterCount));
                break;
            case self::OPERAND_LIKE_CI:
                $query->andWhere($query->expr()->like('LOWER(' . $column . ')', ':__filter' . $filterCount));
                $value = strtolower($value);
                break;

        }

        $query->setParameter('__filter' . $filterCount, $value);
    }
}
 
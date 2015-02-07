<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 02.01.15 - 22:37
 */

namespace SqlConnector\Service;

use Application\DataType\SqlConnector\TableRow;
use Application\SharedKernel\MessageMetadata;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Prooph\Processing\Functional\Iterator\MapIterator;
use Prooph\Processing\Message\AbstractWorkflowMessageHandler;
use Prooph\Processing\Message\ProcessingMessage;
use Prooph\Processing\Message\LogMessage;
use Prooph\Processing\Message\WorkflowMessage;
use Prooph\Processing\Type\Description\Description;
use Prooph\Processing\Type\Description\NativeType;
use Prooph\Processing\Type\Prototype;
use Prooph\Processing\Type\Type;
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
     * Key in collect-data single result metadata. If set to TRUE the TableGateway uses the identifier name of the processing type to find a result
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
     * Note: The table of the requested processing type is aliased as "main"
     * Note: Don't set limit, offset or order_by. Use metadata for these settings, because the TableGateway performs also a count query without offset, limit and order_by
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
    const META_OFFSET = MessageMetadata::OFFSET;

    /**
     * Set the query limit
     */
    const META_LIMIT = MessageMetadata::LIMIT;

    /**
     * Set order_by like you would do in a SQL query.
     *
     * example: "age DESC,name ASC"
     */
    const META_ORDER_BY = "order_by";

    /**
     * Flag to empty table before insert
     */
    const META_EMPTY_TABLE = "empty_table";

    /**
     * Activate update or insert processing
     *
     * This will take much longer than empty table and insert,
     * so use it only for delta updates.
     */
    const META_TRY_UPDATE = "try_update";

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
     * method and uses the returned ProcessingMessage as response
     *
     * @param WorkflowMessage $workflowMessage
     * @return ProcessingMessage
     */
    protected function handleCollectData(WorkflowMessage $workflowMessage)
    {
        try {
            return $this->collectData($workflowMessage);
        } catch (\Exception $ex) {
            ErrorHandler::stop();
            return LogMessage::logException($ex, $workflowMessage);
        }
    }

    /**
     * If workflow message handler receives a process-data message it forwards the message to this
     * method and uses the returned ProcessingMessage as response
     *
     * @param WorkflowMessage $workflowMessage
     * @return ProcessingMessage
     */
    protected function handleProcessData(WorkflowMessage $workflowMessage)
    {
        try {
            return $this->processData($workflowMessage);
        } catch (\Exception $ex) {
            ErrorHandler::stop();
            return LogMessage::logException($ex, $workflowMessage);
        }
    }

    /**
     * @param WorkflowMessage $workflowMessage
     * @return WorkflowMessage
     */
    private function collectData(WorkflowMessage $workflowMessage)
    {
        $processingType = $workflowMessage->payload()->getTypeClass();

        /** @var $desc Description */
        $desc = $processingType::buildDescription();

        switch ($desc->nativeType()) {
            case NativeType::COLLECTION:
                return $this->collectResultSet($workflowMessage);
                break;
            case NativeType::DICTIONARY:
                return $this->collectSingleResult($workflowMessage);
                break;
            default:
                return LogMessage::logUnsupportedMessageReceived($workflowMessage);
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

        $stmt = $query->execute();

        //Premap row, so that factory fromDatabaseRow is used to construct the TableRow type
        $mapIterator = new MapIterator($stmt, function ($item) use ($itemType) {
            return $itemType::fromDatabaseRow($item);
        });

        $collection = $collectionType::fromNativeValue($mapIterator);

        return $workflowMessage->answerWith($collection, [MessageMetadata::TOTAL_ITEMS => $count]);
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
            if (isset($metadata[MessageMetadata::OFFSET])) {
                $query->setFirstResult($metadata[MessageMetadata::OFFSET]);
            }

            if (isset($metadata[MessageMetadata::LIMIT])) {
                $query->setMaxResults($metadata[MessageMetadata::LIMIT]);
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

    /**
     * @param WorkflowMessage $message
     * @return LogMessage|WorkflowMessage
     */
    private function processData(WorkflowMessage $message)
    {
        $metadata = $message->metadata();

        if (isset($metadata[self::META_EMPTY_TABLE]) && $metadata[self::META_EMPTY_TABLE]) {
            $this->emptyTable();
        }

        $forceInsert = true;

        if (isset($metadata[self::META_TRY_UPDATE]) && $metadata[self::META_TRY_UPDATE]) {
            $forceInsert = false;
        }

        return $this->updateOrInsertPayload($message, $forceInsert);
    }

    /**
     * @param WorkflowMessage $message
     * @param bool $forceInsert
     * @return LogMessage|WorkflowMessage
     */
    private function updateOrInsertPayload(WorkflowMessage $message, $forceInsert = false)
    {
        $processingType = $message->payload()->getTypeClass();

        /** @var $desc Description */
        $desc = $processingType::buildDescription();

        $successful = 0;
        $failed = 0;
        $failedMessages = [];

        if ($desc->nativeType() == NativeType::COLLECTION) {

            /** @var $prototype Prototype */
            $prototype = $processingType::prototype();

            $itemProto = $prototype->typeProperties()['item']->typePrototype();

            /** @var $tableRow TableRow */
            foreach ($message->payload()->toType() as $i => $tableRow) {
                if (! $tableRow instanceof TableRow) {
                    return LogMessage::logUnsupportedMessageReceived($message);
                }

                try {
                    $this->updateOrInsertTableRow($tableRow, $forceInsert);

                    $successful++;
                } catch (\Exception $e) {
                    $datasetIndex = ($tableRow->description()->hasIdentifier())?
                        $tableRow->description()->identifierName() . " = " . $tableRow->property($tableRow->description()->identifierName())->value()
                        : $i;

                    $failed++;
                    $failedMessages[] = sprintf(
                        'Dataset %s: %s',
                        $datasetIndex,
                        $e->getMessage()
                    );
                }
            }

            $report = [
                MessageMetadata::SUCCESSFUL_ITEMS => $successful,
                MessageMetadata::FAILED_ITEMS => $failed,
                MessageMetadata::FAILED_MESSAGES => $failedMessages
            ];

            if ($failed > 0) {
                return LogMessage::logItemsProcessingFailed(
                    $successful,
                    $failed,
                    $failedMessages,
                    $message
                );
            } else {
                return $message->answerWithDataProcessingCompleted($report);
            }
        } else {
            $tableRow = $message->payload()->toType();

            if (! $tableRow instanceof TableRow) {
                return LogMessage::logUnsupportedMessageReceived($message);
            }

            $this->updateOrInsertTableRow($tableRow, $forceInsert);

            return $message->answerWithDataProcessingCompleted();
        }
    }

    private function updateOrInsertTableRow(TableRow $data, $forceInsert = false)
    {
        $id = false;
        $pk = null;

        if ($data->description()->hasIdentifier()) {
            $pk = $data::toDbColumnName($data->description()->identifierName());

            $id = $data->property($data->description()->identifierName())->value();
        }

        $dbTypes = $this->getDbTypesForProperties($data);

        if ($id) {

            $count = 0;

            if (! $forceInsert) {
                $query = $this->connection->createQueryBuilder();

                $query->select('COUNT(*)')
                    ->from($this->table)
                    ->where(
                        $query->expr()->eq(
                            $pk,
                            ':identifier'
                        )
                    );

                $query->setParameter('identifier', $id, $dbTypes[$pk]);

                $count = $query->execute()->fetchColumn();
            }

            if ($count) {
                //We add one additional type for the pk
                $dbTypesArr = array_values($dbTypes);
                $dbTypesArr[] = $dbTypes[$pk];

                $this->connection->update(
                    $this->table,
                    $this->convertToDbData($data),
                    [$pk => $id],
                    $dbTypesArr
                );
            } else {
                $this->connection->insert(
                    $this->table,
                    $this->convertToDbData($data),
                    array_values($dbTypes)
                );
            }
        } else { //insert without id, useful if column is auto incremented or table has no pk
            $dbData = $this->convertToDbData($data);

            if ($pk) {
                unset($dbData[$pk]);
                unset($dbTypes[$pk]);
            }

            $this->connection->insert($this->table, $dbData, array_values($dbTypes));
        }
    }

    private function convertToDbData(TableRow $tableRow)
    {
        $data = [];

        /** @var $prop Type */
        foreach ($tableRow->value() as $propName => $prop) {
            $data[$tableRow::toDbColumnName($propName)] = $prop->value();
        }

        return $data;
    }

    /**
     * Db types need to be an array in the same order as the properties
     * @param TableRow $tableRow
     * @return array
     */
    private function getDbTypesForProperties(TableRow $tableRow)
    {
        $dbTypes = [];

        foreach ($tableRow->properties() as $propName => $prop) {
            $dbTypes[$tableRow::toDbColumnName($propName)] = $tableRow::getDbTypeForProperty($propName);
        }

        return $dbTypes;
    }

    private function emptyTable()
    {
        $foreignKeys = [];

        $dbPlatform = $this->connection->getDatabasePlatform();

        if ($dbPlatform->supportsForeignKeyConstraints()) {
            $sm = $this->connection->getSchemaManager();

            $foreignKeys = $sm->listTableForeignKeys($this->table);

            foreach ($foreignKeys as $foreignKey) {
                $sm->dropForeignKey($foreignKey, $this->table);
            }
        }

        $q = $dbPlatform->getTruncateTableSql($this->table);
        $this->connection->executeUpdate($q);

        if (! empty($foreignKeys)) {
            $sm = $this->connection->getSchemaManager();
            foreach ($foreignKeys as $foreignKey) {
                $sm->createForeignKey($foreignKey, $this->table);
            }
        }
    }
}
 
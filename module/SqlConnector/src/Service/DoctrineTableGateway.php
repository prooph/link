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
final class DoctrineTableGateway implements WorkflowMessageHandler
{
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
     * @param WorkflowMessage $aWorkflowMessage
     * @return void
     */
    public function handleWorkflowMessage(WorkflowMessage $aWorkflowMessage)
    {
        try {
            switch ($aWorkflowMessage->getMessageType()) {
                case MessageNameUtils::COLLECT_DATA:
                    $this->collectData($aWorkflowMessage);
                    break;
                case MessageNameUtils::PROCESS_DATA:

                    break;
                default:
                    $this->eventBus->dispatch(LogMessage::logUnsupportedMessageReceived($aWorkflowMessage, 'sqlconnector-' . $this->table));
            }
        } catch (\Exception $ex) {
            $this->eventBus->dispatch(LogMessage::logException($ex, $aWorkflowMessage->getProcessTaskListPosition()));
        }
    }

    /**
     * @param WorkflowMessage $workflowMessage
     */
    private function collectData(WorkflowMessage $workflowMessage)
    {
        $gingerType = $workflowMessage->getPayload()->getTypeClass();

        /** @var $desc Description */
        $desc = $gingerType::buildDescription();

        switch ($desc->nativeType()) {
            case NativeType::COLLECTION:
                $this->collectResultSet($workflowMessage);
                break;
            case NativeType::DICTIONARY:
                $this->collectSingleResult($workflowMessage);
                break;
            default:
                $this->eventBus->dispatch(LogMessage::logUnsupportedMessageReceived($workflowMessage, 'sqlconnector-' . $this->table));
        }
    }

    private function collectSingleResult(WorkflowMessage $workflowMessage)
    {

    }

    private function collectResultSet(WorkflowMessage $workflowMessage)
    {
        $count = $this->countRows($workflowMessage->getMetadata());

        $query = $this->buildQueryFromMetadata($workflowMessage->getMetadata());

        $resultSet = $query->execute()->fetchAll();

        /** @var $collectionType AbstractCollection */
        $collectionType = $workflowMessage->getPayload()->getTypeClass();

        $itemType = $collectionType::prototype()->propertiesOfType()['item']->typePrototype()->of();

        if (! method_exists($itemType, 'fromDatabaseRow')) throw new \InvalidArgumentException(sprintf("Item type %s does not provide a static fromDatabaseRow factory method", $itemType));

        $items = [];

        foreach ( $resultSet as $itemData) {
            $items[] = $itemType::fromDatabaseRow($itemData);
        }

        $collection = $collectionType::fromNativeValue($items);

        $this->eventBus->dispatch($workflowMessage->answerWith($collection, ['total_items' => $count]));
    }

    /**
     * @param array $metadata
     * @return int
     */
    private function countRows(array $metadata)
    {
        $query = $this->buildQueryFromMetadata($metadata, true);

        return $query->execute()->fetchColumn();
    }

    /**
     * @param array $metadata
     * @param bool $countMode
     * @throws \InvalidArgumentException
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function buildQueryFromMetadata(array $metadata, $countMode = false)
    {
        ErrorHandler::start();

        $query = $this->connection->createQueryBuilder();

        if ($countMode) {
            $query->select('COUNT(*)');
        } else {
            $query->select('*');
        }

        $query->from($this->table, 'main');

        if (isset($metadata['filter']) && is_array($metadata['filter'])) {
            $filterCount = 0;

            foreach ($metadata['filter'] as $column => $value) {
                $filterCount++;

                if (is_array($value)) {
                    if (! array_key_exists('operand', $value)) throw new \InvalidArgumentException(sprintf('Missing operand in filter array for column %s', $column));
                    if (! array_key_exists('value', $value)) throw new \InvalidArgumentException(sprintf('Missing value in filter array for column %s', $column));

                    if (isset($value['column'])) {
                        $column = $value['column'];
                    }

                    $this->addFilter($query, $column, $value['operand'], $value['value'], $filterCount);
                } else {
                    $this->addFilter($query, $column, '=', $value, $filterCount);
                }
            }
        }

        if (isset($metadata['query_builder_script']) && file_exists($metadata['query_builder_script'])) {
            $queryBuilderFunc = include($metadata['query_builder_script']);
            if (is_callable($queryBuilderFunc)) $queryBuilderFunc($query, $metadata);
        }

        if (! $countMode) {
            if (isset($metadata['offset'])) {
                $query->setFirstResult($metadata['offset']);
            }

            if (isset($metadata['limit'])) {
                $query->setMaxResults($metadata['limit']);
            }

            if (isset($metadata['order_by'])) {
                $orderByArr = explode(",", $metadata['order_by']);

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
            case '=':
                $query->andWhere($query->expr()->eq($column, ':__filter' . $filterCount));
                break;
            case '<':
                $query->andWhere($query->expr()->lt($column, ':__filter' . $filterCount));
                break;
            case '<=':
                $query->andWhere($query->expr()->lte($column, ':__filter' . $filterCount));
                break;
            case '>':
                $query->andWhere($query->expr()->gt($column, ':__filter' . $filterCount));
                break;
            case '>=':
                $query->andWhere($query->expr()->gte($column, ':__filter' . $filterCount));
                break;
            case 'like':
                $query->andWhere($query->expr()->like($column, ':__filter' . $filterCount));
                break;
            case 'ilike':
                $query->andWhere($query->expr()->like('LOWER(' . $column . ')', ':__filter' . $filterCount));
                $value = strtolower($value);
                break;

        }

        $query->setParameter('__filter' . $filterCount, $value);
    }

    /**
     * Register command bus that can be used to send new commands to the workflow processor
     *
     * @param CommandBus $commandBus
     * @return void
     */
    public function useCommandBus(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * Register event bus that can be used to send events to the workflow processor
     *
     * @param EventBus $eventBus
     * @return void
     */
    public function useEventBus(EventBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }
}
 
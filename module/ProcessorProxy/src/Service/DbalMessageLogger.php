<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.01.15 - 01:13
 */

namespace ProcessorProxy\Service;

use Doctrine\DBAL\Connection;
use Ginger\Message\GingerMessage;
use Ginger\Processor\ProcessId;
use ProcessorProxy\Model\MessageLogEntry;
use ProcessorProxy\Model\MessageLogger;
use Prooph\ServiceBus\Message\MessageInterface;
use Rhumsaa\Uuid\Uuid;

/**
 * Class DbalMessageLogger
 *
 * @package ProcessorProxy\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class DbalMessageLogger implements MessageLogger
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $entriesByMessageId;

    const TABLE_NAME = "messages";

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Uuid $messageId
     * @return null|MessageLogEntry
     */
    public function getEntryForMessageId(Uuid $messageId)
    {
        if (isset($this->entriesByMessageId[$messageId->toString()])) return $this->entriesByMessageId[$messageId->toString()];

        $query = $this->connection->createQueryBuilder();

        $query->select('*')->from(self::TABLE_NAME)->where($query->expr()->eq('message_id', ':message_id'));

        $query->setParameter('message_id', $messageId->toString());

        $entryData = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        if (!$entryData) return null;

        $entry = MessageLogEntry::fromArray($entryData);

        $this->entriesByMessageId[$messageId->toString()] = $entry;

        return $entry;
    }

    /**
     * @param MessageInterface|GingerMessage $message
     * @return void
     */
    public function logIncomingMessage($message)
    {
        $entry = MessageLogEntry::logMessage($message);

        $this->connection->insert(self::TABLE_NAME, $entry->toArray());

        $this->entriesByMessageId[$entry->messageId()->toString()] = $entry;
    }

    /**
     * @param ProcessId $processId
     * @param Uuid $messageId
     * @throws \InvalidArgumentException
     * @return void
     */
    public function logProcessStartedByMessage(ProcessId $processId, Uuid $messageId)
    {
        $entry = $this->getEntryForMessageId($messageId);

        if (is_null($entry)) throw new \InvalidArgumentException(sprintf("A log entry for message with id %s can not be found", $messageId->toString()));

        $entry->assignIdOfStartedProcess($processId);

        $success = $this->updateEntry($entry);

        if (!$success) throw new \InvalidArgumentException(sprintf('Logging process id for start message %s failed.', $messageId->toString()));

        $this->entriesByMessageId[$messageId->toString()] = $entry;
    }

    /**
     * @param Uuid $messageId
     * @throws \InvalidArgumentException
     * @return void
     */
    public function logMessageProcessingSucceed(Uuid $messageId)
    {
        $entry = $this->getEntryForMessageId($messageId);

        if (is_null($entry)) throw new \InvalidArgumentException(sprintf("A log entry for message with id %s can not be found", $messageId->toString()));

        $entry->markAsSucceed();

        $success = $this->updateEntry($entry);

        if (!$success) throw new \InvalidArgumentException(sprintf('Mark log entry for message %s as succeed failed.', $messageId->toString()));

        $this->entriesByMessageId[$messageId->toString()] = $entry;
    }

    /**
     * @param Uuid $messageId
     * @param string $failureMsg
     * @throws \InvalidArgumentException
     * @return void
     */
    public function logMessageProcessingFailed(Uuid $messageId, $failureMsg)
    {
        $entry = $this->getEntryForMessageId($messageId);

        if (is_null($entry)) throw new \InvalidArgumentException(sprintf("A log entry for message with id %s can not be found", $messageId->toString()));

        $entry->markAsFailed($failureMsg);

        $success = $this->updateEntry($entry);

        if (!$success) throw new \InvalidArgumentException(sprintf('Mark log entry for message %s as -failed- failed.', $messageId->toString()));

        $this->entriesByMessageId[$messageId->toString()] = $entry;
    }

    /**
     * @param MessageLogEntry $entry
     * @return int affected rows
     */
    private function updateEntry(MessageLogEntry $entry)
    {
        return $this->connection->update(self::TABLE_NAME, $entry->toArray(), ["message_id" => $entry->messageId()->toString()]);
    }
}
 
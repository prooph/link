<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 20.01.15 - 23:29
 */

namespace Prooph\Link\Monitor\Service;

use Doctrine\DBAL\Connection;
use Prooph\Processing\Processor\ProcessId;
use Prooph\Link\Monitor\Model\ProcessLogger;

/**
 * Class DbalProcessLogger
 *
 * @package Prooph\Link\Monitor\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class DbalProcessLogger implements ProcessLogger
{
    const TABLE = "processes";

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Cached entries
     *
     * @var array
     */
    private $entries = [];

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Create or update entry for process with the start message name.
     * If a new process needs to be created set status to "running".
     *
     * @param string $startMessageName
     * @param ProcessId $processId
     * @return void
     */
    public function logProcessStartedByMessage(ProcessId $processId, $startMessageName)
    {
        $data = [
            'start_message' => $startMessageName,
        ];

        $entry = $this->loadProcessEntryIfExists($processId);

        if (is_null($entry)) {
            $data['status'] = self::STATUS_RUNNING;

            $this->insertNewEntry($processId, $data);
        } else {
            $this->updateEntry($processId, $data);
        }
    }

    /**
     * Create or update entry for process with the started at information.
     * If a new process needs to be created set status to "running".
     *
     * @param ProcessId $processId
     * @param \DateTime $startedAt
     * @return void
     */
    public function logProcessStartedAt(ProcessId $processId, \DateTime $startedAt)
    {
        $data = [
            'started_at' => $startedAt->format(\DateTime::ISO8601),
        ];

        $entry = $this->loadProcessEntryIfExists($processId);

        if (is_null($entry)) {
            $data['status'] = self::STATUS_RUNNING;

            $this->insertNewEntry($processId, $data);
        } else {
            $this->updateEntry($processId, $data);
        }
    }

    /**
     * Create or update entry for process with finished at information.
     * Set status to "succeed".
     *
     * @param ProcessId $processId
     * @param \DateTime $finishedAt
     * @return void
     */
    public function logProcessSucceed(ProcessId $processId, \DateTime $finishedAt)
    {
        $data = [
            'finished_at' => $finishedAt->format(\DateTime::ISO8601),
            'status' => self::STATUS_SUCCEED,
        ];

        $entry = $this->loadProcessEntryIfExists($processId);

        if (is_null($entry)) {
            $this->insertNewEntry($processId, $data);
        } else {
            $this->updateEntry($processId, $data);
        }
    }

    /**
     * Create or update entry for process with finished at information.
     * Set status to "failed".
     *
     * @param ProcessId $processId
     * @param \DateTime $finishedAt
     * @return void
     */
    public function logProcessFailed(ProcessId $processId, \DateTime $finishedAt)
    {
        $data = [
            'finished_at' => $finishedAt->format(\DateTime::ISO8601),
            'status' => self::STATUS_FAILED,
        ];

        $entry = $this->loadProcessEntryIfExists($processId);

        if (is_null($entry)) {
            $this->insertNewEntry($processId, $data);
        } else {
            $this->updateEntry($processId, $data);
        }
    }

    /**
     * Orders process logs by started_at DESC
     * Returns array of process log entry arrays.
     * Each process log contains the information:
     *
     * - process_id => UUID string
     * - status => running|succeed|failed
     * - start_message => string|null
     * - started_at => \DateTime::ISO8601 formatted
     * - finished_at =>  \DateTime::ISO8601 formatted
     *
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getLastLoggedProcesses($offset = 0, $limit = 10)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('*')->from(self::TABLE)->orderBy('started_at', 'DESC')->setFirstResult($offset)->setMaxResults($limit);

        return $query->execute()->fetchAll();
    }

    /**
     * @param ProcessId $processId
     * @return null|array process log, see {@method getLastLoggedProcesses} for structure
     */
    public function getLoggedProcess(ProcessId $processId)
    {
        return $this->loadProcessEntryIfExists($processId);
    }

    /**
     * @param ProcessId $processId
     * @param array $data
     */
    private function insertNewEntry(ProcessId $processId, array $data)
    {
        $data['process_id'] = $processId->toString();

        $this->connection->insert(self::TABLE, $data);

        $this->entries[$processId->toString()] = $data;
    }

    /**
     * @param ProcessId $processId
     * @param array $data
     */
    private function updateEntry(ProcessId $processId, array $data)
    {
        $this->connection->update(self::TABLE, $data, ['process_id' => $processId->toString()]);

        $this->entries[$processId->toString()] = array_merge($this->entries[$processId->toString()], $data);
    }

    /**
     * @param ProcessId $processId
     * @return null|array
     */
    private function loadProcessEntryIfExists(ProcessId $processId)
    {
        if (isset($this->entries[$processId->toString()])) return $this->entries[$processId->toString()];

        $query = $this->connection->createQueryBuilder();

        $query->select('*')
            ->from(self::TABLE)
            ->where('process_id = :process_id')
            ->setParameter('process_id', $processId->toString());

        $entry = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        if (!$entry) {
            return null;
        }

        $this->entries[$processId->toString()] = $entry;

        return $entry;
    }
}
 
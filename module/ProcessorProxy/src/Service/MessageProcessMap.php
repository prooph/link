<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.01.15 - 01:13
 */

namespace ProcessorProxy\Service;

use Doctrine\DBAL\Connection;

/**
 * Class MessageProcessMap
 *
 * @package ProcessorProxy\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class MessageProcessMap 
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $entriesByMessageId;

    const TABLE_NAME = "message_process_map";

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $messageId
     * @param string $processId
     * @param bool $succeed
     */
    public function addEntry($messageId, $processId, $succeed)
    {
        $this->connection->insert("message_process_map", [
            "message_id" => $messageId,
            "process_id" => $processId,
            "succeed"    => (int)$succeed,
        ]);
    }

    /**
     * @param $messageId
     * @return array
     */
    public function getEntryForMessageId($messageId)
    {
        if (isset($this->entriesByMessageId[$messageId])) return $this->entriesByMessageId[$messageId];

        $query = $this->connection->createQueryBuilder();

        $query->select('*')->from(self::TABLE_NAME)->where($query->expr()->eq('message_id', ':message_id'));

        $query->setParameter('message_id', $messageId);

        $entry = $query->execute()->fetch();

        if (is_null($entry)) return null;

        $this->cacheEntry($messageId, $entry['process_id'], (bool)$entry['succeed']);

        return $this->entriesByMessageId[$messageId];
    }

    /**
     * @param string $messageId
     * @param string $processId
     * @param bool $succeed
     */
    private function cacheEntry($messageId, $processId, $succeed)
    {
        $this->entriesByMessageId[$messageId] = [
            'message_id' => $messageId,
            'process_id' => $processId,
            'succeed' => $succeed
        ];
    }
}
 
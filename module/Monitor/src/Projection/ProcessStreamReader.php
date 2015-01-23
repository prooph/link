<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/23/15 - 1:43 PM
 */
namespace Gingerwork\Monitor\Projection;

use Ginger\Processor\ProcessId;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\StreamEvent;
use Prooph\EventStore\Stream\StreamName;

/**
 * Class ProcessStreamReader
 *
 * @package Gingerwork\Monitor\Projection
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class ProcessStreamReader
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param EventStore $eventStore
     */
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @param ProcessId $processId
     * @param int $minVersion
     * @return StreamEvent[]
     */
    public function getStreamOfProcess(ProcessId $processId, $minVersion = 0)
    {
        return $this->eventStore->loadEventsByMetadataFrom(
            new StreamName('process_stream'),
            ['aggregate_id' => $processId->toString()],
            $minVersion
        );
    }
} 
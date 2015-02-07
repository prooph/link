<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 20.01.15 - 22:51
 */

namespace Prooph\Link\Monitor\Model;
use Prooph\Processing\Processor\ProcessId;

/**
 * Interface ProcessLogger
 *
 * A ProcessLogger should be able to create a new process log on the fly no matter what information should be logged..
 * All information except the process id and the status should be optional so that information can be added
 * out of order. This is related to the fact that the information is collected based on events which may be received
 * out of order. It is not critical for the process monitor to not have all information in place.
 *
 * @package Prooph\Link\Monitor\src\Model
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface ProcessLogger 
{
    const STATUS_RUNNING = "running";
    const STATUS_SUCCEED = "succeed";
    const STATUS_FAILED  = "failed";

    /**
     * Create or update entry for process with the start message name.
     * If a new process needs to be created set status to "running".
     *
     * @param string $startMessageName
     * @param ProcessId $processId
     * @return void
     */
    public function logProcessStartedByMessage(ProcessId $processId, $startMessageName);

    /**
     * Create or update entry for process with the started at information.
     * If a new process needs to be created set status to "running".
     *
     * @param ProcessId $processId
     * @param \DateTime $startedAt
     * @return void
     */
    public function logProcessStartedAt(ProcessId $processId, \DateTime $startedAt);

    /**
     * Create or update entry for process with finished at information.
     * Set status to "succeed".
     *
     * @param ProcessId $processId
     * @param \DateTime $finishedAt
     * @return void
     */
    public function logProcessSucceed(ProcessId $processId, \DateTime $finishedAt);

    /**
     * Create or update entry for process with finished at information.
     * Set status to "failed".
     *
     * @param ProcessId $processId
     * @param \DateTime $finishedAt
     * @return void
     */
    public function logProcessFailed(ProcessId $processId, \DateTime $finishedAt);

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
    public function getLastLoggedProcesses($offset = 0, $limit = 10);

    /**
     * @param ProcessId $processId
     * @return null|array process log, see {@method getLastLoggedProcesses} for structure
     */
    public function getLoggedProcess(ProcessId $processId);
}
 
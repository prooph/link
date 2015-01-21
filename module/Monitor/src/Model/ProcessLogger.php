<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 20.01.15 - 22:51
 */

namespace Gingerwork\Monitor\Model;
use Ginger\Processor\ProcessId;

/**
 * Interface ProcessLogger
 *
 * A ProcessLogger should be able to create a new process log on the fly no matter what information should be logged..
 * All information except the process id and the status should be optional so that information can be added
 * out of order. This is related to the fact that the information is collected based on events which may be received
 * out of order. It is not critical for the process monitor to not have all information in place.
 *
 * @package Gingerwork\Monitor\src\Model
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
}
 
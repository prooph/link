<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.01.15 - 21:40
 */

namespace ProcessorProxy\Model;

use Ginger\Message\GingerMessage;
use Ginger\Processor\ProcessId;
use Prooph\ServiceBus\Message\MessageInterface;
use Rhumsaa\Uuid\Uuid;

/**
 * Interface DbalMessageLogger
 *
 * @package ProcessorProxy\Model
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface MessageLogger
{
    /**
     * @param Uuid $messageId
     * @return null|MessageLogEntry
     */
    public function getEntryForMessageId(Uuid $messageId);

    /**
     * @param MessageInterface|GingerMessage $message
     * @return void
     */
    public function logIncomingMessage($message);

    /**
     * @param ProcessId $processId
     * @param Uuid $messageId
     * @return void
     */
    public function logProcessStartedByMessage(ProcessId $processId, Uuid $messageId);

    /**
     * @param Uuid $messageId
     * @return void
     */
    public function logMessageProcessingSucceed(Uuid $messageId);

    /**
     * @param Uuid $messageId
     * @param string $failureMsg
     * @return void
     */
    public function logMessageProcessingFailed(Uuid $messageId, $failureMsg);
}
 
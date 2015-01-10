<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.01.15 - 12:40
 */

namespace ProcessorProxy\ProophPlugin;

use Ginger\Message\MessageNameUtils;
use Ginger\Message\WorkflowMessage;
use Ginger\Processor\WorkflowEngine;
use Prooph\ServiceBus\Message\MessageDispatcherInterface;
use Prooph\ServiceBus\Message\MessageInterface;

/**
 * Class InMemoryMessageForwarder
 *
 * This class has has a reference to the ginger workflow engine. Any incoming service bus message is forwarded to
 * the workflow engine by determining the target channel for the message.
 *
 * @package ProcessorProxy\ProophPlugin
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class InMemoryMessageForwarder implements MessageDispatcherInterface
{
    /**
     * @var WorkflowEngine
     */
    private $workflowEngine;

    /**
     * @param MessageInterface $message
     * @return void
     */
    public function dispatch(MessageInterface $message)
    {
        $this->workflowEngine->dispatch($message);
    }
}
 
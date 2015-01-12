<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.01.15 - 00:19
 */

namespace ProcessorProxy\GingerPlugin;

use Doctrine\DBAL\Connection;
use Ginger\Environment\Environment;
use Ginger\Environment\Plugin;
use Ginger\Processor\ProcessId;
use ProcessorProxy\Model\MessageLogger;
use ProcessorProxy\Service\DbalMessageLogger;
use Rhumsaa\Uuid\Uuid;
use Zend\EventManager\Event;

/**
 * Class StartMessageProcessIdLogger
 *
 * The StartMessageProcessIdLogger listens on Ginger\Processor\Processor events to be able to log which
 * message has started which process. The information is required by the UI to redirect the user to
 * the process monitor after sending a start message.
 *
 * @package ProcessorProxy\GingerPlugin
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class StartMessageProcessIdLogger implements Plugin
{
    const PLUGIN_NAME = 'processor_proxy.start_message_process_id_logger';

    /**
     * @var MessageLogger
     */
    private $messageLogger;

    /**
     * @param MessageLogger $messageLogger
     */
    public function __construct(MessageLogger $messageLogger)
    {
        $this->messageLogger = $messageLogger;
    }

    /**
     * Return the name of the plugin
     *
     * @return string
     */
    public function getName()
    {
        return self::PLUGIN_NAME;
    }

    /**
     * Register the plugin on the workflow environment
     *
     * @param Environment $workflowEnv
     * @return void
     */
    public function registerOn(Environment $workflowEnv)
    {
        $workflowEnv->getWorkflowProcessor()->events()->attach("process_was_started_by_message", [$this, "onProcessWasStartedByMessage"]);
    }

    /**
     * @param Event $event
     */
    public function onProcessWasStartedByMessage(Event $event)
    {
        $this->messageLogger->logProcessStartedByMessage(
            ProcessId::fromString($event->getParam('process_id')),
            Uuid::fromString($event->getParam('message_id'))
        );
    }
}
 
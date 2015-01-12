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
use ProcessorProxy\Service\MessageProcessMap;
use Zend\EventManager\Event;

/**
 * Class StartMessageLogger
 *
 * The StartMessageLogger listens on Ginger\Processor\Processor events to be able to log which
 * message has started which process. The information is required by the UI to redirect the user to
 * the process monitor after sending a start message.
 *
 * @package ProcessorProxy\GingerPlugin
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class StartMessageLogger implements Plugin
{
    const PLUGIN_NAME = 'processor_proxy.start_message_logger';

    /**
     * @var MessageProcessMap
     */
    private $messageProcessMap;

    /**
     * @param MessageProcessMap $messageProcessMap
     */
    public function __construct(MessageProcessMap $messageProcessMap)
    {
        $this->messageProcessMap = $messageProcessMap;
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
        $workflowEnv->getWorkflowProcessor()->events()->attach("start_process_from_message_failed", [$this, "onStartProcessFormMessageFailed"]);
    }

    /**
     * @param Event $event
     */
    public function onProcessWasStartedByMessage(Event $event)
    {
        $this->messageProcessMap->addEntry($event->getParam('message_id'), $event->getParam('process_id'), true);
    }

    /**
     * @param Event $event
     */
    public function onStartProcessFormMessageFailed(Event $event)
    {
        $this->messageProcessMap->addEntry($event->getParam('message_id'), $event->getParam('process_id'), false);
    }
}
 
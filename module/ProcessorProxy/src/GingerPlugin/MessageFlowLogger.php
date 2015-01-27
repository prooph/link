<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.01.15 - 22:39
 */

namespace ProcessorProxy\GingerPlugin;

use Ginger\Environment\Environment;
use Ginger\Environment\Plugin;
use Ginger\Message\GingerMessage;
use Ginger\Message\LogMessage;
use Ginger\Message\WorkflowMessage;
use Ginger\Processor\Command\StartSubProcess;
use Ginger\Processor\Event\SubProcessFinished;
use ProcessorProxy\Model\MessageLogEntry;
use ProcessorProxy\Model\MessageLogger;
use Prooph\ServiceBus\Message\MessageInterface;
use Prooph\ServiceBus\Process\CommandDispatch;
use Prooph\ServiceBus\Process\EventDispatch;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

/**
 * Class MessageFlowLogger
 *
 * This logger listens on ginger workflow engine channels to log the message flow of all ginger messages
 *
 * @package ProcessorProxy\GingerPlugin\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class MessageFlowLogger extends AbstractListenerAggregate implements Plugin
{
    const PLUGIN_NAME ="processor_proxy.message_flow_logger";
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
        $workflowEnv->getWorkflowEngine()->attachPluginToAllChannels($this);
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $identifiers = $events->getIdentifiers();

        if (in_array('command_bus', $identifiers)) {
            $this->listeners[] = $events->attach(CommandDispatch::INITIALIZE, array($this, 'onInitializeCommandDispatch'));
            $this->listeners[] = $events->attach(CommandDispatch::FINALIZE, array($this, 'onFinalizeCommandDispatch'));
        }

        if (in_array('event_bus', $identifiers)) {
            $this->listeners[] = $events->attach(EventDispatch::INITIALIZE, array($this, 'onInitializeEventDispatch'));
            $this->listeners[] = $events->attach(EventDispatch::FINALIZE, array($this, 'onFinalizeEventDispatch'));
        }
    }

    /**
     * @param CommandDispatch $commandDispatch
     */
    public function onInitializeCommandDispatch(CommandDispatch $commandDispatch)
    {
        $this->tryLogMessage($commandDispatch->getCommand());
    }

    /**
     * @param EventDispatch $eventDispatch
     */
    public function onInitializeEventDispatch(EventDispatch $eventDispatch)
    {
        $this->tryLogMessage($eventDispatch->getEvent());
    }

    /**
     * @param CommandDispatch $commandDispatch
     */
    public function onFinalizeCommandDispatch(CommandDispatch $commandDispatch)
    {
        if ($ex = $commandDispatch->getException()) {
            $successfulLogged = $this->logMessageProcessingFailed($commandDispatch->getCommand(), $ex);

            if ($successfulLogged) {
                $commandDispatch->setException(null);
            }
        } else {
            $this->logMessageProcessingSucceed($commandDispatch->getCommand());
        }
    }

    public function onFinalizeEventDispatch(EventDispatch $eventDispatch)
    {
        if ($ex = $eventDispatch->getException()) {
            $successfulLogged = $this->logMessageProcessingFailed($eventDispatch->getEvent(), $ex);

            if ($successfulLogged) {
                $eventDispatch->setException(null);
            }
        } else {
            $this->logMessageProcessingSucceed($eventDispatch->getEvent());
        }
    }

    /**
     * Message is only logged if it is has a valid type and is not logged already
     * otherwise it is ignored.
     *
     * @param $message
     */
    private function tryLogMessage($message)
    {
        $messageId = null;

        if ($message instanceof MessageInterface) $messageId = $message->header()->uuid();
        elseif ($message instanceof GingerMessage) $messageId = $message->toServiceBusMessage()->header()->uuid();

        if (! $messageId) return;

        $entry = $this->messageLogger->getEntryForMessageId($messageId);

        if ($entry) return;

        $this->messageLogger->logIncomingMessage($message);
    }

    /**
     * @param $message
     * @param \Exception $ex
     * @return bool if logging was successful
     */
    private function logMessageProcessingFailed($message, \Exception $ex)
    {
        $entry = $this->getLogEntryForMessage($message);

        if (!$entry) return false;

        if (!$entry->status()->isPending()) return false;

        try {
            $this->messageLogger->logMessageProcessingFailed($entry->messageId(), (string)$ex);
        } catch (\Exception $newEx) {
            return false;
        }

    }

    private function logMessageProcessingSucceed($message)
    {
        $entry = $this->getLogEntryForMessage($message);

        if (!$entry) return;

        if (! $entry->status()->isPending()) return;

        $this->messageLogger->logMessageProcessingSucceed($entry->messageId());
    }

    /**
     * @param $message
     * @return null|MessageLogEntry
     */
    private function getLogEntryForMessage($message)
    {
        $messageId = null;

        if ($message instanceof MessageInterface) $messageId = $message->header()->uuid();
        elseif ($message instanceof WorkflowMessage) $messageId = $message->uuid();
        elseif ($message instanceof LogMessage) $messageId = $message->uuid();
        elseif ($message instanceof StartSubProcess) $messageId = $message->uuid();
        elseif ($message instanceof SubProcessFinished) $messageId = $message->uuid();

        if (! $messageId) return null;

        $entry = $this->messageLogger->getEntryForMessageId($messageId);

        if (!$entry) return null;

        return $entry;
    }
}
 
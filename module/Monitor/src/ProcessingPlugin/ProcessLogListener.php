<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 21.01.15 - 16:40
 */

namespace Prooph\Link\Monitor\ProcessingPlugin;

use Prooph\Processing\Environment\Environment;
use Prooph\Processing\Environment\Plugin;
use Prooph\Processing\Processor\ProcessId;
use Prooph\Link\Monitor\Model\ProcessLogger;
use Prooph\EventStore\PersistenceEvent\PostCommitEvent;
use Zend\EventManager\Event;

/**
 * Class ProcessLogListener
 *
 * This class is a Prooph\Processing\Environment\Plugin and acts as a listener for workflow processor events and process events
 * which are persisted by the event store. Main goal is to populate a read model with status information about triggered
 * processes.
 *
 * @package Prooph\Link\Monitor\ProcessingPlugin
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ProcessLogListener implements Plugin
{
    const PLUGIN_NAME = 'prooph.link.monitor.process_log_listener';

    /**
     * @var ProcessLogger
     */
    private $processLogger;

    /**
     * @param ProcessLogger $processLogger
     */
    public function __construct(ProcessLogger $processLogger)
    {
        $this->processLogger = $processLogger;
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
        $workflowEnv->getWorkflowProcessor()->events()->attach('process_was_started_by_message', [$this, 'onProcessWasStartedByMessage']);
        $workflowEnv->getWorkflowProcessor()->events()->attach('process_did_finish', [$this, 'onProcessDidFinish']);
        $workflowEnv->getEventStore()->getPersistenceEvents()->attach('commit.post', [$this, 'onEventStorePostCommit']);
    }

    /**
     * @param Event $e
     */
    public function onProcessWasStartedByMessage(Event $e)
    {
        $this->processLogger->logProcessStartedByMessage(
            ProcessId::fromString($e->getParam('process_id')),
            $e->getParam('message_name')
        );
    }

    /**
     * @param PostCommitEvent $e
     */
    public function onEventStorePostCommit(PostCommitEvent $e)
    {
        foreach ($e->getRecordedEvents() as $recordedEvent) {
            if ($recordedEvent->eventName()->toString() === 'Prooph\Processing\Processor\Event\ProcessWasSetUp') {
                $this->processLogger->logProcessStartedAt(
                    ProcessId::fromString($recordedEvent->payload()['aggregate_id']),
                    $recordedEvent->occurredOn()
                );
            }
        }
    }

    /**
     * @param Event $e
     */
    public function onProcessDidFinish(Event $e)
    {
        if ($e->getParam('succeed')) {
            $this->processLogger->logProcessSucceed(
                ProcessId::fromString($e->getParam('process_id')),
                \DateTime::createFromFormat(\DateTime::ISO8601, $e->getParam('finished_at'))
            );
        } else {
            $this->processLogger->logProcessFailed(
                ProcessId::fromString($e->getParam('process_id')),
                \DateTime::createFromFormat(\DateTime::ISO8601, $e->getParam('finished_at'))
            );
        }
    }
}
 
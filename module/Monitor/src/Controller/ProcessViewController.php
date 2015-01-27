<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 21.01.15 - 20:59
 */

namespace Gingerwork\Monitor\Controller;

use Application\Service\AbstractQueryController;
use Application\Service\TranslatorAwareController;
use Application\SharedKernel\LocationTranslator;
use Application\SharedKernel\ProcessToClientTranslator;
use Application\SharedKernel\ScriptLocation;
use Dashboard\Controller\AbstractWidgetController;
use Dashboard\View\DashboardWidget;
use Ginger\Processor\ProcessId;
use Ginger\Processor\Task\TaskListPosition;
use Gingerwork\Monitor\Model\ProcessLogger;
use Gingerwork\Monitor\Projection\ProcessStreamReader;
use Prooph\EventStore\Stream\StreamEvent;
use Verraes\ClassFunctions\ClassFunctions;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\ViewModel;

/**
 * Class ProcessViewController
 *
 * @package Gingerwork\Monitor\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ProcessViewController extends AbstractQueryController implements TranslatorAwareController
{
    /**
     * @var ProcessLogger
     */
    private $processLogger;

    /**
     * @var ProcessStreamReader
     */
    private $processStreamReader;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var ScriptLocation
     */
    private $scriptLocation;

    /**
     * @var LocationTranslator
     */
    private $locationTranslator;

    /**
     * @param ProcessLogger $processLogger
     * @param ProcessStreamReader $processStreamReader
     * @param ScriptLocation $scriptLocation
     * @param LocationTranslator $locationTranslator
     */
    public function __construct(
        ProcessLogger $processLogger,
        ProcessStreamReader $processStreamReader,
        ScriptLocation $scriptLocation,
        LocationTranslator $locationTranslator
    ) {
        $this->processLogger = $processLogger;
        $this->processStreamReader = $processStreamReader;
        $this->scriptLocation = $scriptLocation;
        $this->locationTranslator = $locationTranslator;
    }

    public function detailsAction()
    {
        $processId = ProcessId::fromString($this->params('process_id'));
        $process = $this->processLogger->getLoggedProcess($processId);

        if (is_null($process)) {
            return $this->notFoundAction();
        }

        $process['events'] = $this->convertToClientProcessEvents($this->processStreamReader->getStreamOfProcess($processId));

        if (! isset($process['start_message']) || ! isset($this->systemConfig->getProcessDefinitions()[$process['start_message']])) {
            return $this->incompleteAction($process);
        }

        $definition = $this->convertToClientProcess(
            $process['start_message'],
            $this->systemConfig->getProcessDefinitions()[$process['start_message']],
            $this->systemConfig->getAllAvailableGingerTypes());

        $process = array_merge($process, $definition);

        $this->populateTaskEvents($process);

        $view = new ViewModel(
            [
                'process' => $process,
                'available_ginger_types' => $this->getGingerTypesForClient(),
                'available_task_types' => \Ginger\Processor\Definition::getAllTaskTypes(),
                'available_manipulation_scripts' => $this->scriptLocation->getScriptNames(),
                'connectors' => $this->systemConfig->getConnectors(),
                'locations'  => $this->locationTranslator->getLocations(),
            ]
        );

        $view->setTemplate('gingerwork/monitor/process-view/process-details-app');

        $this->layout()->setVariable('includeEmberJs', true);

        return $view;
    }

    /**
     * @param array $process
     * @return ViewModel
     */
    private function incompleteAction(array $process)
    {
        $view = new ViewModel(['process' => $process]);
        $view->setTemplate('gingerwork/monitor/process-view/process-details-incomplete');
        return $view;
    }

    /**
     * @param StreamEvent[] $streamEvents
     * @return array
     */
    private function convertToClientProcessEvents(array $streamEvents)
    {
        $clientEvents = [];

        foreach ($streamEvents as $streamEvent) {
            $clientEvent = [
                'name' => ClassFunctions::short($streamEvent->eventName()->toString()),
                'process_id' => $streamEvent->metadata()['aggregate_id'],
                'payload' => $streamEvent->payload(),
                'occurred_on' => $streamEvent->occurredOn()->format(\DateTime::ISO8601),
            ];

            $taskListPosition = null;

            if (isset($clientEvent['payload']['taskListPosition'])) {
                $taskListPosition = TaskListPosition::fromString($clientEvent['payload']['taskListPosition']);
            }

            $clientEvent['task_list_id'] = ($taskListPosition)? $taskListPosition->taskListId()->toString() : null;
            $clientEvent['task_id'] = ($taskListPosition)? $taskListPosition->position() : null;

            $clientEvents[] = $clientEvent;
        }

        return $clientEvents;
    }

    /**
     * @param string $startMessage
     * @param array $processDefinition
     * @param array $knownGingerTypes
     * @return array
     */
    private function convertToClientProcess($startMessage, array $processDefinition, array $knownGingerTypes)
    {
        return ProcessToClientTranslator::translate($startMessage, $processDefinition, $knownGingerTypes, $this->scriptLocation);
    }

    /**
     * Copy each task event to its task
     *
     * @param array $process
     */
    private function populateTaskEvents(array &$process)
    {
        foreach ($process['tasks'] as &$task) {
            $task['events'] = [];
            foreach ($process['events'] as $event) {
                if ($event['task_id'] == $task['id']) {
                    $task['events'][] = $event;
                }
            }
        }
    }

    /**
     * @param Translator $translator
     * @return void
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }
}
 
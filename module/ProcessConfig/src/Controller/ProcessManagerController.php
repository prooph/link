<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.12.14 - 19:35
 */

namespace ProcessConfig\Controller;

use Application\Service\AbstractQueryController;
use Application\SharedKernel\DataTypeClass;
use Application\SharedKernel\ScriptLocation;
use Ginger\Functional\Func;
use Ginger\Message\MessageNameUtils;
use Ginger\Processor\Definition;
use SystemConfig\Projection\GingerConfig;
use SystemConfig\Service\NeedsSystemConfig;
use ZF\ContentNegotiation\ViewModel;

/**
 * Class ProcessManagerController
 *
 * @package ProcessConfig\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ProcessManagerController extends AbstractQueryController implements NeedsSystemConfig
{
    /**
     * @var GingerConfig
     */
    private $systemConfig;

    /**
     * @var ScriptLocation
     */
    private $scriptLocation;

    public function startAppAction()
    {
        $viewModel = new ViewModel([
            'processes' => Func::map(
                    $this->systemConfig->getProcessDefinitions(),
                    function($definition, $message) {
                        return $this->convertToClientProcess($message, $definition, $this->systemConfig->getAllPossibleDataTypes());
                    }
                ),
            'possible_data_types' => $this->prepareDataTypesSelect($this->systemConfig->getAllPossibleDataTypes()),
            'possible_task_types' => \Ginger\Processor\Definition::getAllTaskTypes(),
            'possible_manipulation_scripts' => $this->scriptLocation->getScriptNames(),
            'connectors' => $this->systemConfig->getConnectors(),
        ]);

        $viewModel->setTemplate('process-config/process-manager/app');

        return $viewModel;
    }

    /**
     * @param string $startMessage
     * @param array $processDefinition
     * @param array $knownDataTypes
     * @return array
     */
    private function convertToClientProcess($startMessage, array $processDefinition, array $knownDataTypes)
    {
        $messageType = MessageNameUtils::getMessageSuffix($startMessage);

        foreach($processDefinition['tasks'] as $i => &$task) {
            $task['id'] = $i+1;
        }


        return [
            'id'  => $startMessage,
            'name' => $processDefinition['name'],
            'processType' => $processDefinition['process_type'],
            'startMessage' => [
                'messageType' => $messageType,
                'dataType' => DataTypeClass::extractFromMessageName($startMessage, $knownDataTypes)
            ],
            'tasks' => array_map(
                function ($task) {
                    if ($task['task_type'] === Definition::TASK_MANIPULATE_PAYLOAD) {
                        $task['manipulation_script'] = str_replace($this->scriptLocation->toString() . DIRECTORY_SEPARATOR, "", $task['manipulation_script']);
                    }

                    return $task;
                },
                $processDefinition['tasks']
            )
        ];
    }

    /**
     * @param array $dataTypes
     * @return array
     */
    private function prepareDataTypesSelect(array $dataTypes)
    {
        $preparedDataTypes = [];

        foreach ($dataTypes as $dataTypeClass) {
            $preparedDataTypes[] = [
                'value' => $dataTypeClass,
                'label' => $dataTypeClass::prototype()->typeDescription()->label()
            ];
        }

        return $preparedDataTypes;
    }

    /**
     * @param GingerConfig $systemConfig
     * @return void
     */
    public function setSystemConfig(GingerConfig $systemConfig)
    {
        $this->systemConfig = $systemConfig;
    }

    /**
     * @param ScriptLocation $scriptLocation
     */
    public function setScriptLocation(ScriptLocation $scriptLocation)
    {
        $this->scriptLocation = $scriptLocation;
    }
}
 
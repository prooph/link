<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.12.14 - 19:35
 */

namespace ProcessConfig\Controller;

use Application\Service\AbstractQueryController;
use Application\Service\TranslatorAwareController;
use Application\SharedKernel\ProcessingTypeClass;
use Application\SharedKernel\LocationTranslator;
use Application\SharedKernel\ProcessToClientTranslator;
use Application\SharedKernel\ScriptLocation;
use Prooph\Processing\Functional\Func;
use Prooph\Processing\Message\MessageNameUtils;
use Prooph\Processing\Processor\Definition;
use Prooph\Processing\Processor\LinearProcess;
use Prooph\Processing\Type\Description\Description;
use Prooph\Processing\Type\Prototype;
use Prooph\Processing\Type\PrototypeProperty;
use SystemConfig\Projection\ProcessingConfig;
use SystemConfig\Service\ConfigWriter\ZendPhpArrayWriter;
use SystemConfig\Service\NeedsSystemConfig;
use Zend\Mvc\I18n\Translator;
use ZF\ContentNegotiation\ViewModel;

/**
 * Class ProcessManagerController
 *
 * @package ProcessConfig\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ProcessManagerController extends AbstractQueryController implements TranslatorAwareController
{
    /**
     * @var ScriptLocation
     */
    private $scriptLocation;

    /**
     * @var array
     */
    private $viewAddons;

    /**
     * @var LocationTranslator
     */
    private $locationTranslator;

    /**
     * @var Translator
     */
    private $i18nTranslator;

    public function startAppAction()
    {
        $viewModel = new ViewModel([
            'processes' => array_values(Func::map(
                $this->systemConfig->getProcessDefinitions(),
                function($definition, $message) {
                    return $this->convertToClientProcess($message, $definition, $this->systemConfig->getAllAvailableProcessingTypes());
                }
            )),
            'connectors' => array_values(
                Func::map($this->systemConfig->getConnectors(), function ($connector, $id) {
                    $connector['id'] = $id;
                    return $connector;
                })
            ),
            'available_processing_types' => $this->getProcessingTypesForClient(),
            'available_manipulation_scripts' => $this->scriptLocation->getScriptNames(),
            'locations'  => $this->locationTranslator->getLocations(),
            'available_process_types' => [
                [
                    'value' => \Prooph\Processing\Processor\Definition::PROCESS_LINEAR_MESSAGING,
                    'label' => $this->i18nTranslator->translate('Linear Process'),
                ],
                [
                    'value' => \Prooph\Processing\Processor\Definition::PROCESS_PARALLEL_FOR_EACH,
                    'label' => $this->i18nTranslator->translate('Foreach Process'),
                ],
            ],
            'available_task_types' => [
                [
                    'value' => \Prooph\Processing\Processor\Definition::TASK_COLLECT_DATA,
                    'label' => $this->i18nTranslator->translate('Collect Data'),
                ],
                [
                    'value' => \Prooph\Processing\Processor\Definition::TASK_PROCESS_DATA,
                    'label' => $this->i18nTranslator->translate('Process Data'),
                ],
                [
                    'value' => \Prooph\Processing\Processor\Definition::TASK_MANIPULATE_PAYLOAD,
                    'label' => $this->i18nTranslator->translate('Run Manipulation Script'),
                ],
            ],
            'available_messages' => [
                [
                    'value' => 'collect-data',
                    'label' => $this->i18nTranslator->translate('Collect Data Message'),
                ],
                [
                    'value' => 'data-collected',
                    'label' => $this->i18nTranslator->translate('Data Collected Message'),
                ],
                [
                    'value' => 'process-data',
                    'label' => $this->i18nTranslator->translate('Process Data Message'),
                ],
            ],
        ]);

        $viewModel->setTemplate('process-config/process-manager/app');

        $this->layout()->setVariable('includeRiotJs', true);

        return $viewModel;
    }

    /**
     * @return ViewModel
     */
    public function startTestAppAction()
    {
        //@TODO check development mode
        $this->layout('process-config/process-manager/app-test');

        $fixtures = include(__DIR__ . '/../../tests/data/process-manager-data-fixtures.php');

        $fixtures['view_addons'] = $this->viewAddons;

        $viewModel = new ViewModel($fixtures);

        $viewModel->setTemplate('process-config/process-manager/app');

        return $viewModel;
    }

    /**
     * @param string $startMessage
     * @param array $processDefinition
     * @param array $knownProcessingTypes
     * @return array
     */
    private function convertToClientProcess($startMessage, array $processDefinition, array $knownProcessingTypes)
    {
        return ProcessToClientTranslator::translate($startMessage, $processDefinition, $knownProcessingTypes, $this->scriptLocation);
    }

    /**
     * @param ScriptLocation $scriptLocation
     */
    public function setScriptLocation(ScriptLocation $scriptLocation)
    {
        $this->scriptLocation = $scriptLocation;
    }

    /**
     * @param LocationTranslator $locationTranslator
     */
    public function setLocationTranslator(LocationTranslator $locationTranslator)
    {
        $this->locationTranslator = $locationTranslator;
    }

    /**
     * @param Translator $translator
     * @return void
     */
    public function setTranslator(Translator $translator)
    {
        $this->i18nTranslator = $translator;
    }
}
 
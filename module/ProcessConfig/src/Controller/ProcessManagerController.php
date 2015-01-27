<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.12.14 - 19:35
 */

namespace ProcessConfig\Controller;

use Application\Service\AbstractQueryController;
use Application\SharedKernel\GingerTypeClass;
use Application\SharedKernel\LocationTranslator;
use Application\SharedKernel\ProcessToClientTranslator;
use Application\SharedKernel\ScriptLocation;
use Ginger\Functional\Func;
use Ginger\Message\MessageNameUtils;
use Ginger\Processor\Definition;
use Ginger\Type\Description\Description;
use Ginger\Type\Prototype;
use Ginger\Type\PrototypeProperty;
use SystemConfig\Projection\GingerConfig;
use SystemConfig\Service\ConfigWriter\ZendPhpArrayWriter;
use SystemConfig\Service\NeedsSystemConfig;
use ZF\ContentNegotiation\ViewModel;

/**
 * Class ProcessManagerController
 *
 * @package ProcessConfig\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ProcessManagerController extends AbstractQueryController
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

    public function startAppAction()
    {
        $viewModel = new ViewModel([
            'processes' => Func::map(
                    $this->systemConfig->getProcessDefinitions(),
                    function($definition, $message) {
                        return $this->convertToClientProcess($message, $definition, $this->systemConfig->getAllAvailableGingerTypes());
                    }
                ),
            'available_ginger_types' => $this->getGingerTypesForClient(),
            'available_task_types' => \Ginger\Processor\Definition::getAllTaskTypes(),
            'available_manipulation_scripts' => $this->scriptLocation->getScriptNames(),
            'connectors' => $this->systemConfig->getConnectors(),
            'locations'  => $this->locationTranslator->getLocations(),
            'view_addons' => $this->viewAddons
        ]);

        $viewModel->setTemplate('process-config/process-manager/app');

        $this->layout()->setVariable('includeEmberJs', true);

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
     * @param array $knownGingerTypes
     * @return array
     */
    private function convertToClientProcess($startMessage, array $processDefinition, array $knownGingerTypes)
    {
        return ProcessToClientTranslator::translate($startMessage, $processDefinition, $knownGingerTypes, $this->scriptLocation);
    }

    /**
     * @param ScriptLocation $scriptLocation
     */
    public function setScriptLocation(ScriptLocation $scriptLocation)
    {
        $this->scriptLocation = $scriptLocation;
    }

    /**
     * @param array $viewAddons
     */
    public function setViewAddons(array $viewAddons)
    {
        $this->viewAddons = $viewAddons;
    }

    /**
     * @param LocationTranslator $locationTranslator
     */
    public function setLocationTranslator(LocationTranslator $locationTranslator)
    {
        $this->locationTranslator = $locationTranslator;
    }
}
 
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
use Application\SharedKernel\ConfigLocation;
use Application\SharedKernel\DataTypeClass;
use Ginger\Functional\Func;
use Ginger\Message\MessageNameUtils;
use SystemConfig\Definition;
use SystemConfig\Model\GingerConfig;
use ZF\ContentNegotiation\ViewModel;

/**
 * Class ProcessManagerController
 *
 * @package ProcessConfig\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ProcessManagerController extends AbstractQueryController
{
    public function startAppAction()
    {
        $gingerConfig = GingerConfig::asProjectionFrom(ConfigLocation::fromPath(Definition::SYSTEM_CONFIG_DIR));

        $viewModel = new ViewModel([
            'processes' => Func::map(
                    $gingerConfig->getProcessDefinitions(),
                    function($definition, $message) use ($gingerConfig) {
                        return $this->convertToClientProcess($message, $definition, $gingerConfig->getAllPossibleGingerTypes());
                    }
                ),
            'possible_ginger_types' => $gingerConfig->getAllPossibleGingerTypes()
        ]);

        $viewModel->setTemplate('process-config/process-manager/app');

        return $viewModel;
    }

    /**
     * @param $startMessage
     * @param array $processDefinition
     * @param array $knownDataTypes
     * @return array
     */
    private function convertToClientProcess($startMessage, array $processDefinition, array $knownDataTypes)
    {
        $messageType = MessageNameUtils::getMessageSuffix($startMessage);

        return [
            'id'  => $startMessage,
            'name' => $processDefinition['name'],
            'process_type' => $processDefinition['process_type'],
            'start_message' => [
                'message_type' => $messageType,
                'data_type' => DataTypeClass::extractFromMessageName($startMessage, $knownDataTypes)
            ],
            'tasks' => $processDefinition['tasks']
        ];
    }
}
 
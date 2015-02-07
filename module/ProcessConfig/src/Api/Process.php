<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/14/14 - 9:41 PM
 */
namespace ProcessConfig\Api;

use Application\Service\AbstractRestController;
use Application\Service\ActionController;
use Application\SharedKernel\ScriptLocation;
use Prooph\Processing\Message\MessageNameUtils;
use Prooph\Processing\Processor\Definition;
use Prooph\ServiceBus\CommandBus;
use SystemConfig\Command\AddNewProcessToConfig;
use SystemConfig\Command\ChangeProcessConfig;
use SystemConfig\Projection\ProcessingConfig;
use SystemConfig\Service\NeedsSystemConfig;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

/**
 * Class Process
 *
 * @package ProcessConfig\Api
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class Process extends AbstractRestController implements ActionController, NeedsSystemConfig
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var ProcessingConfig
     */
    private $systemConfig;

    /**
     * @var ScriptLocation
     */
    private $scriptLocation;

    public function create($data)
    {
        if (! array_key_exists("process", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Root key process missing in request data'));

        $data = $data["process"];

        $result = $this->validateProcessData($data);

        if ($result instanceof ApiProblemResponse) return $result;

        $startMessage = $this->generateStartMessage($data);

        $command = AddNewProcessToConfig::fromDefinition(
            $data['name'],
            $data['process_type'],
            $startMessage,
            $data['tasks'],
            $this->systemConfig->getConfigLocation()
        );

        $this->commandBus->dispatch($command);

        $this->getResponse()->setStatusCode(201);

        $data["id"] = $startMessage;

        return ["process" => $data];
    }

    /**
     * @param string $id
     * @param array $data
     * @return mixed|void
     */
    public function update($id, $data)
    {
        if (! $this->existsProcess($id)) return new ApiProblemResponse(new ApiProblem(404, 'Process can not be found'));

        if (! array_key_exists("process", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Root key process missing in request data'));

        $data = $data["process"];

        $startMessage = $this->generateStartMessage($data);

        if ($id !== $startMessage) return new ApiProblemResponse(new ApiProblem(422, 'Changing the start message is not allowed. You have to create a new process and remove the old one!'));

        $result = $this->validateProcessData($data);

        if ($result instanceof ApiProblemResponse) return $result;

        $this->commandBus->dispatch(ChangeProcessConfig::ofProcessTriggeredByMessage(
            $startMessage,
            $this->translateToProcessConfig($data),
            $this->systemConfig->getConfigLocation()
        ));

        $data["id"] = $startMessage;

        return ["process" => $data];
    }

    /**
     * @param CommandBus $commandBus
     * @return void
     */
    public function setCommandBus(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param ProcessingConfig $systemConfig
     * @return void
     */
    public function setSystemConfig(ProcessingConfig $systemConfig)
    {
        $this->systemConfig = $systemConfig;
    }

    /**
     * @param string $startMessage
     * @return bool
     */
    private function existsProcess($startMessage)
    {
        return array_key_exists($startMessage, $this->systemConfig->getProcessDefinitions());
    }

    /**
     * @param array $data
     * @return string
     */
    private function generateStartMessage(array $data)
    {
        return "processing-message-" . MessageNameUtils::normalize($data["start_message"]["processing_type"]) . '-' . $data["start_message"]["message_type"];
    }

    private function validateProcessData(array $data)
    {
        if (! array_key_exists("name", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Process name missing in request data'));
        if (! array_key_exists("process_type", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Process type missing in request data'));
        if (! array_key_exists("start_message", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Start message missing in request data'));
        if (! is_array($data["start_message"])) return new ApiProblemResponse(new ApiProblem(422, 'Start message must be an array'));
        if (! array_key_exists("message_type", $data["start_message"])) return new ApiProblemResponse(new ApiProblem(422, 'Message type missing in start message definition'));
        if (! array_key_exists("processing_type", $data["start_message"])) return new ApiProblemResponse(new ApiProblem(422, 'Data type missing in start message definition'));
        if (! array_key_exists("tasks", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Tasks missing in request data'));
        if (! is_array($data["tasks"])) return new ApiProblemResponse(new ApiProblem(422, 'Tasks must be an array'));

        return null;
    }

    /**
     * @param array $data
     * @return array
     */
    private function translateToProcessConfig(array $data)
    {
        return [
            'name' => $data["name"],
            'process_type' => $data["process_type"],
            'tasks' => array_map(
                function($task) {
                    if ($task['task_type'] === Definition::TASK_MANIPULATE_PAYLOAD) {
                        if (isset($task['manipulation_script'])) {
                            $task['manipulation_script'] = $this->scriptLocation->toString() . DIRECTORY_SEPARATOR
                                . $task['manipulation_script'];
                        }
                    }
                    unset($task['id']); return $task;
                },
                $data['tasks']
            ),
        ];
    }

    /**
     * @param ScriptLocation $scriptLocation
     */
    public function setScriptLocation(ScriptLocation $scriptLocation)
    {
        $this->scriptLocation = $scriptLocation;
    }
}
<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/14/14 - 9:41 PM
 */
namespace ProcessConfig\Api;

use Application\Service\AbstractRestController;
use Application\Service\ActionController;
use Ginger\Message\MessageNameUtils;
use Prooph\ServiceBus\CommandBus;
use SystemConfig\Command\AddNewProcessToConfig;
use SystemConfig\Command\ChangeProcessConfig;
use SystemConfig\Projection\GingerConfig;
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
     * @var GingerConfig
     */
    private $systemConfig;

    public function create($data)
    {
        if (! array_key_exists("process", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Root key process missing in request data'));

        $data = $data["process"];

        $result = $this->validateProcessData($data);

        if ($result instanceof ApiProblemResponse) return $result;

        $startMessage = $this->generateStartMessage($data);

        $command = AddNewProcessToConfig::fromDefinition(
            $data['name'],
            $data['processType'],
            $startMessage,
            $data['tasks'],
            $this->systemConfig->getConfigLocation()
        );

        $this->commandBus->dispatch($command);

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
     * @param GingerConfig $systemConfig
     * @return void
     */
    public function setSystemConfig(GingerConfig $systemConfig)
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
        return "ginger-message-" . MessageNameUtils::normalize($data["startMessage"]["dataType"]) . '-' . $data["startMessage"]["messageType"];
    }

    private function validateProcessData(array $data)
    {
        if (! array_key_exists("name", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Process name missing in request data'));
        if (! array_key_exists("processType", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Process type missing in request data'));
        if (! array_key_exists("startMessage", $data)) return new ApiProblemResponse(new ApiProblem(422, 'StartMessage missing in request data'));
        if (! is_array($data["startMessage"])) return new ApiProblemResponse(new ApiProblem(422, 'StartMessage must be an array'));
        if (! array_key_exists("messageType", $data["startMessage"])) return new ApiProblemResponse(new ApiProblem(422, 'Message type missing in start message definition'));
        if (! array_key_exists("dataType", $data["startMessage"])) return new ApiProblemResponse(new ApiProblem(422, 'Data type missing in start message definition'));
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
            'process_type' => $data["processType"],
            'tasks' => array_map(function($task) {unset($task['id']); return $task;}, $data['tasks']),
        ];
    }
}
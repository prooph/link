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
use Application\SharedKernel\ConfigLocation;
use Ginger\Message\MessageNameUtils;
use Prooph\ServiceBus\CommandBus;
use SystemConfig\Command\AddNewProcessToConfig;
use SystemConfig\Definition;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

/**
 * Class Process
 *
 * @package ProcessConfig\Api
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class Process extends AbstractRestController implements ActionController
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    public function create($data)
    {
        if (! array_key_exists("process", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Root key process missing in request data'));

        $data = $data["process"];

        if (! array_key_exists("name", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Process name missing in request data'));
        if (! array_key_exists("processType", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Process type missing in request data'));
        if (! array_key_exists("startMessage", $data)) return new ApiProblemResponse(new ApiProblem(422, 'StartMessage missing in request data'));
        if (! is_array($data["startMessage"])) return new ApiProblemResponse(new ApiProblem(422, 'StartMessage must be an array'));
        if (! array_key_exists("messageType", $data["startMessage"])) return new ApiProblemResponse(new ApiProblem(422, 'Message type missing in start message definition'));
        if (! array_key_exists("dataType", $data["startMessage"])) return new ApiProblemResponse(new ApiProblem(422, 'Data type missing in in start message definition'));
        if (! array_key_exists("tasks", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Tasks missing in request data'));
        if (! is_array($data["tasks"])) return new ApiProblemResponse(new ApiProblem(422, 'Tasks must be an array'));

        $startMessage = "ginger-message-" . MessageNameUtils::normalize($data["startMessage"]["dataType"]) . '-' . $data["startMessage"]["messageType"];

        $command = AddNewProcessToConfig::fromDefinition(
            $data['name'],
            $data['processType'],
            $startMessage,
            $data['tasks'],
            ConfigLocation::fromPath(Definition::SYSTEM_CONFIG_DIR)
        );

        $this->commandBus->dispatch($command);

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
}
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.01.15 - 21:49
 */

namespace ProcessorProxy\Api;

use Application\Service\AbstractRestController;
use Application\Service\ActionController;
use ProcessorProxy\Command\ForwardHttpMessage;
use ProcessorProxy\Model\MessageLogger;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Message\StandardMessage;
use Rhumsaa\Uuid\Uuid;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

/**
 * Class Message
 *
 * @package ProcessorProxy\Api
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class Message extends AbstractRestController implements ActionController
{
    /**
     * @var CommandBus
     */
    private $commandBus;

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
     * @param array $data
     * @return mixed|void
     */
    public function create(array $data)
    {
        $message = StandardMessage::fromArray($data);

        $this->commandBus->dispatch(ForwardHttpMessage::createWith($message));

        //@TODO: improve response, provide get service which returns status of the message including related actions
        //@TODO: like started process etc.
        return $message->toArray();
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function get($id)
    {
        $message = $this->messageLogger->getEntryForMessageId(Uuid::fromString($id));

        if (is_null($message)) return new ApiProblemResponse(new ApiProblem(404, "Message can not be found"));

        return ["message" => $message->toArray()];
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
 
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.01.15 - 21:49
 */

namespace ProcessorProxy\Api;

use Application\Service\ActionController;
use Ginger\Environment\Environment;
use Ginger\Message\MessageNameUtils;
use Ginger\Message\WorkflowMessage;
use ProcessorProxy\Command\ForwardHttpMessage;
use ProcessorProxy\Service\DbalMessageLogger;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Message\StandardMessage;
use SqlConnector\DataType\GingerTestSource\TartikelCollection;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

/**
 * Class Message
 *
 * @package ProcessorProxy\Api
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class Message extends AbstractRestfulController implements ActionController
{
    /**
     * @var CommandBus
     */
    private $commandBus;



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
     * @param CommandBus $commandBus
     * @return void
     */
    public function setCommandBus(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    private function logMessage()
    {

    }
}
 
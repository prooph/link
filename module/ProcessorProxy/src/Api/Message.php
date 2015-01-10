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

use Ginger\Environment\Environment;
use Ginger\Message\WorkflowMessage;
use Prooph\ServiceBus\Message\StandardMessage;
use Zend\Mvc\Controller\AbstractRestfulController;

/**
 * Class Message
 *
 * @package ProcessorProxy\Api
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class Message extends AbstractRestfulController
{
    public function create(array $data)
    {
        $message = WorkflowMessage::fromServiceBusMessage(StandardMessage::fromArray($data));

        $env = Environment::setUp($this->getServiceLocator());

        $env->getWorkflowProcessor()->receiveMessage($message);
    }
}
 
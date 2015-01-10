<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.01.15 - 00:43
 */

namespace ProcessorProxyTest\Command;

use Ginger\Message\LogMessage;
use Ginger\Message\WorkflowMessage;
use Ginger\Processor\Command\StartSubProcess;
use Ginger\Processor\NodeName;
use Ginger\Processor\ProcessId;
use Ginger\Processor\Task\TaskListId;
use Ginger\Processor\Task\TaskListPosition;
use ProcessorProxy\Command\ForwardHttpMessage;
use ProcessorProxyTest\DataType\TestUser;
use ProcessorProxyTest\TestCase;
use Prooph\ServiceBus\Message\FromMessageTranslator;
use Prooph\ServiceBus\Message\ToMessageTranslator;

/**
 * Class ForwardMessageTest
 *
 * @package ProcessorProxyTest\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ForwardMessageTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_created_from_a_service_bus_message_to_wrap_it()
    {
        $workflowMessage = WorkflowMessage::collectDataOf(TestUser::prototype());

        $sbMessage = $workflowMessage->toServiceBusMessage();

        $forwardMessage = ForwardHttpMessage::createWith($sbMessage);

        $this->assertSame($sbMessage, $forwardMessage->forwardedMessage());
    }
}
 
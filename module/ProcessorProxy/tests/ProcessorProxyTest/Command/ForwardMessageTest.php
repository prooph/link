<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.01.15 - 00:43
 */

namespace ProcessorProxyTest\Command;

use Prooph\Processing\Message\LogMessage;
use Prooph\Processing\Message\WorkflowMessage;
use Prooph\Processing\Processor\Command\StartSubProcess;
use Prooph\Processing\Processor\NodeName;
use Prooph\Processing\Processor\ProcessId;
use Prooph\Processing\Processor\Task\TaskListId;
use Prooph\Processing\Processor\Task\TaskListPosition;
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
        $workflowMessage = WorkflowMessage::collectDataOf(
            TestUser::prototype(),
            'test-case',
            'localhost'
        );

        $sbMessage = $workflowMessage->toServiceBusMessage();

        $forwardMessage = ForwardHttpMessage::createWith($sbMessage);

        $this->assertSame($sbMessage, $forwardMessage->forwardedMessage());
    }
}
 
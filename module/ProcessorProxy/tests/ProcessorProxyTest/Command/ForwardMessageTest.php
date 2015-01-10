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
use ProcessorProxy\Command\ForwardMessage;
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
     * @dataProvider provideGingerMessages
     */
    public function it_can_be_created_from_any_ginger_message_translated_to_a_service_bus_message_and_back($gingerMessage)
    {
        $forwardMessage = ForwardMessage::createFrom($gingerMessage);

        $this->assertSame($gingerMessage, $forwardMessage->forwardedMessage());

        $toSbMessageTranslator = new ToMessageTranslator();
        $fromSbMessageTranslator = new FromMessageTranslator();

        $this->assertTrue($toSbMessageTranslator->canTranslateToMessage($forwardMessage));

        $sbMessage = $toSbMessageTranslator->translateToMessage($forwardMessage);

        $reconstitutedForwardMessage = $fromSbMessageTranslator->translateFromMessage($sbMessage);

        $this->assertEquals($forwardMessage->uuid()->toString(), $reconstitutedForwardMessage->uuid()->toString());

        $uuidGetter = ($gingerMessage instanceof StartSubProcess)? "uuid" : "getUuid";

        $this->assertEquals($forwardMessage->forwardedMessage()->{$uuidGetter}(), $reconstitutedForwardMessage->forwardedMessage()->{$uuidGetter}());
    }

    /**
     * @return array
     */
    public function provideGingerMessages()
    {
        return [
            [
                WorkflowMessage::collectDataOf(TestUser::prototype())
            ],
            [
                LogMessage::logErrorMsg("a test error", $this->generateTaskListPosition())
            ],
            [
                StartSubProcess::at($this->generateTaskListPosition(), ["process_type" => "faked_process_definition"], true)
            ]
        ];
    }

    /**
     * @return TaskListPosition
     */
    private function generateTaskListPosition()
    {
        return TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);
    }
}
 
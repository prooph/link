<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.01.15 - 12:35
 */

namespace ProcessorProxyTest\ProophPlugin;

use Ginger\Message\WorkflowMessage;
use ProcessorProxy\Command\ForwardHttpMessage;
use ProcessorProxy\ProophPlugin\ServiceBusMessageExtractor;
use ProcessorProxyTest\TestCase;
use ProcessorProxyTest\DataType\TestUser;

/**
 * Class ServiceBusMessageExtractorTest
 *
 * @package ProcessorProxyTest\ProophPlugin
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ServiceBusMessageExtractorTest extends TestCase
{
    /**
     * @test
     */
    public function it_extracts_a_service_bus_message_from_a_forward_message_command()
    {
        $wfMessage = WorkflowMessage::collectDataOf(TestUser::prototype());

        $sbMessage = $wfMessage->toServiceBusMessage();

        $forwardMessage = ForwardHttpMessage::createWith($sbMessage);

        $messageExtractor = new ServiceBusMessageExtractor();

        $this->assertTrue($messageExtractor->canTranslateToMessage($forwardMessage));

        $this->assertSame($sbMessage, $messageExtractor->translateToMessage($forwardMessage));
    }
}
 
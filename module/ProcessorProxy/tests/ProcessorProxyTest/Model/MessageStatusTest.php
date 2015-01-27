<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.01.15 - 18:03
 */

namespace ProcessorProxyTest\Model;

use ProcessorProxy\Model\MessageStatus;
use ProcessorProxyTest\TestCase;

/**
 * Class MessageStatusTest
 *
 * @package ProcessorProxyTest\Model
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class MessageStatusTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_created_as_pending_status_and_has_no_failure_msg()
    {
        $status = MessageStatus::pending();

        $this->assertTrue($status->isPending());
        $this->assertEquals(MessageStatus::PENDING, $status->toString());
        $this->assertNull($status->failureMsg());
    }

    /**
     * @test
     */
    public function it_can_be_marked_as_succeed_when_it_is_in_pending_status()
    {
        $status = MessageStatus::pending();

        $status = $status->markAsSucceed();

        $this->assertTrue($status->isSucceed());
        $this->assertEquals(MessageStatus::SUCCEED, $status->toString());
        $this->assertNull($status->failureMsg());
    }

    /**
     * @test
     */
    public function it_can_be_marked_as_failed_with_a_failure_msg_when_it_is_in_pending_status()
    {
        $status = MessageStatus::pending();

        $status = $status->markAsFailed("Message failed");

        $this->assertTrue($status->isFailed());
        $this->assertEquals(MessageStatus::FAILED, $status->toString());
        $this->assertEquals("Message failed", $status->failureMsg());
    }

    /**
     * @test
     */
    public function it_can_be_created_from_array()
    {
        $status = MessageStatus::fromArray(["status" => MessageStatus::FAILED, "failure_msg" => "Message failed"]);

        $this->assertTrue($status->isFailed());
        $this->assertEquals(MessageStatus::FAILED, $status->toString());
        $this->assertEquals("Message failed", $status->failureMsg());
    }
}
 
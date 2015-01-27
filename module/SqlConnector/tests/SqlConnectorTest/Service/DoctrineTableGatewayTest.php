<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 00:51
 */

namespace SqlConnectorTest\Service;

use Ginger\Message\WorkflowMessage;
use Ginger\Processor\NodeName;
use Ginger\Processor\ProcessId;
use Ginger\Processor\ProophPlugin\SingleTargetMessageRouter;
use Ginger\Processor\ProophPlugin\WorkflowProcessorInvokeStrategy;
use Ginger\Processor\RegistryWorkflowEngine;
use Ginger\Processor\Task\TaskListId;
use Ginger\Processor\Task\TaskListPosition;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use SqlConnector\Service\DoctrineTableGateway;
use SqlConnectorTest\DataType\TestUser;
use SqlConnectorTest\DataType\TestUserCollection;
use SqlConnectorTest\Fixture\UsersFixture;
use SqlConnectorTest\Mock\StupidWorkflowProcessorMock;
use SqlConnectorTest\TestCase;

/**
 * Class DoctrineTableGatewayTest
 *
 * @package SqlConnectorTest\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class DoctrineTableGatewayTest extends TestCase
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var StupidWorkflowProcessorMock
     */
    private $messageReceiver;

    /**
     * @var DoctrineTableGateway
     */
    private $tableGateway;

    protected function setUp()
    {
        UsersFixture::createTableAndInsertUsers($this->getDbalConnection());

        $this->messageReceiver = new StupidWorkflowProcessorMock();

        $this->commandBus = new CommandBus();

        $this->commandBus->utilize(new SingleTargetMessageRouter($this->messageReceiver));

        $this->commandBus->utilize(new WorkflowProcessorInvokeStrategy());

        $this->eventBus = new EventBus();

        $this->eventBus->utilize(new SingleTargetMessageRouter($this->messageReceiver));

        $this->eventBus->utilize(new WorkflowProcessorInvokeStrategy());

        $this->tableGateway = new DoctrineTableGateway($this->getDbalConnection(), 'users');

        $workflowEngine = new RegistryWorkflowEngine();

        $workflowEngine->registerCommandBus($this->commandBus, [NodeName::defaultName()->toString()]);
        $workflowEngine->registerEventBus($this->eventBus, [NodeName::defaultName()->toString()]);

        $this->tableGateway->useWorkflowEngine($workflowEngine);
    }

    protected function tearDown()
    {
        UsersFixture::dropTable($this->getDbalConnection());
        $this->messageReceiver->reset();
    }

    /**
     * @test
     */
    public function it_collects_all_users_when_requested_ginger_type_is_a_collection_and_wf_message_contains_no_metadata()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $message = WorkflowMessage::collectDataOf(TestUserCollection::prototype());

        $message->connectToProcessTask($taskListPosition);

        $this->tableGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $userCollection = $wfMessage->payload()->toType();

        $this->assertInstanceOf('SqlConnectorTest\DataType\TestUserCollection', $userCollection);

        $this->assertEquals(3, count($userCollection->value()));
        $this->assertEquals(3, $wfMessage->metadata()['total_items']);

        foreach ($userCollection->value() as $testUser) {
            $this->assertInstanceOf('SqlConnectorTest\DataType\TestUser', $testUser);
        }
    }

    /**
     * @test
     */
    public function it_filters_users_when_metadata_contains_a_simple_filter_definition()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $metadata = [
            'filter' => [
                'name' => 'Donald Duck'
            ]
        ];

        $message = WorkflowMessage::collectDataOf(TestUserCollection::prototype(), $metadata);

        $message->connectToProcessTask($taskListPosition);

        $this->tableGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $userCollection = $wfMessage->payload()->toType();

        $this->assertInstanceOf('SqlConnectorTest\DataType\TestUserCollection', $userCollection);

        $this->assertEquals(1, count($userCollection->value()));
        $this->assertEquals(1, $wfMessage->metadata()['total_items']);
        $this->assertEquals(
            ['Donald Duck'],
            array_map(
                function (TestUser $user) { return $user->property('name')->value(); },
                $userCollection->value()
            )
        );
    }

    /**
     * @test
     */
    public function it_filters_users_when_metadata_contains_a_case_insensitive_like_filter()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $metadata = [
            'filter' => [
                'name' => [
                    'operand' => 'ilike',
                    'value'   => '%do%'
                ]
            ]
        ];

        $message = WorkflowMessage::collectDataOf(TestUserCollection::prototype(), $metadata);

        $message->connectToProcessTask($taskListPosition);

        $this->tableGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $userCollection = $wfMessage->payload()->toType();

        $this->assertInstanceOf('SqlConnectorTest\DataType\TestUserCollection', $userCollection);

        $this->assertEquals(2, count($userCollection->value()));
        $this->assertEquals(2, $wfMessage->metadata()['total_items']);
        $this->assertEquals(
            ['John Doe', 'Donald Duck'],
            array_map(
                function (TestUser $user) { return $user->property('name')->value(); },
                $userCollection->value()
            )
        );
    }

    /**
     * @test
     */
    public function it_filters_users_by_a_between_filter()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $metadata = [
            'filter' => [
                'age_min' => [
                    'column' => 'age',
                    'operand' => '>=',
                    'value'   => 30
                ],
                'age_max' => [
                    'column' => 'age',
                    'operand' => '<',
                    'value' => 50
                ]
            ]
        ];

        $message = WorkflowMessage::collectDataOf(TestUserCollection::prototype(), $metadata);

        $message->connectToProcessTask($taskListPosition);

        $this->tableGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $userCollection = $wfMessage->payload()->toType();

        $this->assertInstanceOf('SqlConnectorTest\DataType\TestUserCollection', $userCollection);

        $this->assertEquals(2, count($userCollection->value()));
        $this->assertEquals(2, $wfMessage->metadata()['total_items']);
        $this->assertEquals(
            ['John Doe', 'Max Mustermann'],
            array_map(
                function (TestUser $user) { return $user->property('name')->value(); },
                $userCollection->value()
            )
        );
    }

    /**
     * @test
     */
    public function it_collects_chunk_of_users_but_provides_a_total_count_in_metadata_when_offset_and_limit_are_present_in_metadata()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $metadata = [
            'offset' => 0,
            'limit' => 2
        ];

        $message = WorkflowMessage::collectDataOf(TestUserCollection::prototype(), $metadata);

        $message->connectToProcessTask($taskListPosition);

        $this->tableGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $userCollection = $wfMessage->payload()->toType();

        $this->assertInstanceOf('SqlConnectorTest\DataType\TestUserCollection', $userCollection);

        $this->assertEquals(2, count($userCollection->value()));
        $this->assertEquals(3, $wfMessage->metadata()['total_items']);
        $this->assertEquals(
            ['John Doe', 'Max Mustermann'],
            array_map(
                function (TestUser $user) { return $user->property('name')->value(); },
                $userCollection->value()
            )
        );
    }

    /**
     * @test
     */
    public function it_orders_users_by_the_defined_order_given_in_the_metadata()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $metadata = [
            'order_by' => 'age DESC',
        ];

        $message = WorkflowMessage::collectDataOf(TestUserCollection::prototype(), $metadata);

        $message->connectToProcessTask($taskListPosition);

        $this->tableGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $userCollection = $wfMessage->payload()->toType();

        $this->assertInstanceOf('SqlConnectorTest\DataType\TestUserCollection', $userCollection);

        $this->assertEquals(3, count($userCollection->value()));
        $this->assertEquals(3, $wfMessage->metadata()['total_items']);

        $expectedOrderedNames = ['Donald Duck', 'Max Mustermann', 'John Doe'];

        foreach ($userCollection->value() as $user) {
            $this->assertEquals(array_shift($expectedOrderedNames), $user->property('name')->value());
        }
    }

    /**
     * @test
     */
    public function it_collects_the_first_row_if_a_table_row_type_is_requested_and_no_filter_is_given()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $message = WorkflowMessage::collectDataOf(TestUser::prototype());

        $message->connectToProcessTask($taskListPosition);

        $this->tableGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $user = $wfMessage->payload()->toType();

        $this->assertInstanceOf('SqlConnectorTest\DataType\TestUser', $user);

        $this->assertEquals('John Doe', $user->property('name')->value());
    }

    /**
     * @test
     */
    public function it_collects_user_identified_by_its_id_when_identifier_is_given_in_metadata()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $metadata = ['identifier' => 2];

        $message = WorkflowMessage::collectDataOf(TestUser::prototype(), $metadata);

        $message->connectToProcessTask($taskListPosition);

        $this->tableGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $user = $wfMessage->payload()->toType();

        $this->assertInstanceOf('SqlConnectorTest\DataType\TestUser', $user);

        $this->assertEquals('Max Mustermann', $user->property('name')->value());
    }

    /**
     * @test
     */
    public function it_collects_the_oldest_user_by_using_order_by_age()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $metadata = ['order_by' => 'age DESC'];

        $message = WorkflowMessage::collectDataOf(TestUser::prototype(), $metadata);

        $message->connectToProcessTask($taskListPosition);

        $this->tableGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $user = $wfMessage->payload()->toType();

        $this->assertInstanceOf('SqlConnectorTest\DataType\TestUser', $user);

        $this->assertEquals('Donald Duck', $user->property('name')->value());
    }
}
 
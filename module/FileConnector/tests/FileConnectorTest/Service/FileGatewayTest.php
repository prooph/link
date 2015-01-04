<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 04.01.15 - 17:48
 */

namespace FileConnectorTest\Service;

use FileConnector\Service\FileGateway;
use FileConnectorTest\Bootstrap;
use FileConnectorTest\DataType\TestUser;
use FileConnectorTest\DataType\TestUserCollection;
use FileConnectorTest\Mock\StupidWorkflowProcessorMock;
use FileConnectorTest\TestCase;
use Ginger\Message\WorkflowMessage;
use Ginger\Processor\NodeName;
use Ginger\Processor\ProcessId;
use Ginger\Processor\ProophPlugin\SingleTargetMessageRouter;
use Ginger\Processor\ProophPlugin\WorkflowProcessorInvokeStrategy;
use Ginger\Processor\Task\TaskListId;
use Ginger\Processor\Task\TaskListPosition;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;

final class FileGatewayTest extends TestCase
{
    /**
     * @var FileGateway
     */
    private $fileGateway;

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


    protected function setUp()
    {
        $this->messageReceiver = new StupidWorkflowProcessorMock();

        $this->commandBus = new CommandBus();

        $this->commandBus->utilize(new SingleTargetMessageRouter($this->messageReceiver));

        $this->commandBus->utilize(new WorkflowProcessorInvokeStrategy());

        $this->eventBus = new EventBus();

        $this->eventBus->utilize(new SingleTargetMessageRouter($this->messageReceiver));

        $this->eventBus->utilize(new WorkflowProcessorInvokeStrategy());

        $this->fileGateway = new FileGateway(Bootstrap::getServiceManager()->get('fileconnector.file_type_adapter_manager'));

        $this->fileGateway->useCommandBus($this->commandBus);

        $this->fileGateway->useEventBus($this->eventBus);
    }

    protected function tearDown()
    {
        $this->messageReceiver->reset();
    }

    /**
     * @test
     */
    public function it_collects_users_from_a_single_csv_file()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $metadata = [
            'filename_pattern' => '/^users-header\.csv$/',
            'path' => $this->getTestDataPath(),
            'file_type' => 'csv'
        ];

        $message = WorkflowMessage::collectDataOf(TestUserCollection::prototype(), $metadata);

        $message->connectToProcessTask($taskListPosition);

        $this->fileGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $userCollection = $wfMessage->getPayload()->toType();

        $this->assertInstanceOf('FileConnectorTest\DataType\TestUserCollection', $userCollection);

        $this->assertEquals(3, count($userCollection->value()));
        $this->assertEquals(3, $wfMessage->getMetadata()['total_items']);

        foreach ($userCollection->value() as $testUser) {
            $this->assertInstanceOf('FileConnectorTest\DataType\TestUser', $testUser);
        }
    }

    /**
     * @test
     */
    public function it_collects_users_from_multiple_json_files()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $metadata = [
            'filename_pattern' => '/^testuser_.+\.json$/',
            'path' => $this->getTestDataPath(),
            'file_type' => 'json',
            'fetch_mode' => FileGateway::FETCH_MODE_MULTI_FILES,
            'file_data_type' => TestUser::prototype()->of(),
        ];

        $message = WorkflowMessage::collectDataOf(TestUserCollection::prototype(), $metadata);

        $message->connectToProcessTask($taskListPosition);

        $this->fileGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $userCollection = $wfMessage->getPayload()->toType();

        $this->assertInstanceOf('FileConnectorTest\DataType\TestUserCollection', $userCollection);

        $this->assertEquals(2, count($userCollection->value()));
        $this->assertEquals(2, $wfMessage->getMetadata()['total_items']);

        foreach ($userCollection->value() as $testUser) {
            $this->assertInstanceOf('FileConnectorTest\DataType\TestUser', $testUser);
        }
    }

    /**
     * @TODO: Improve test, the merge_files strategy is not the best choice for the tested scenario
     * @test
     */
    public function it_collects_users_from_multiple_json_files_using_merge_files_strategy()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $metadata = [
            'filename_pattern' => '/^testuser_.+\.json$/',
            'path' => $this->getTestDataPath(),
            'file_type' => 'json',
            'fetch_mode' => FileGateway::FETCH_MODE_MULTI_FILES,
            'merge_files' => true
        ];

        $message = WorkflowMessage::collectDataOf(TestUser::prototype(), $metadata);

        $message->connectToProcessTask($taskListPosition);

        $this->fileGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $user = $wfMessage->getPayload()->toType();

        $this->assertInstanceOf('FileConnectorTest\DataType\TestUser', $user);

        //The FileGateway should have found both user files. It uses scandir with descending order so
        //testuser_max_mustermann.json should be handled before testuser_john_doe.json.
        //When merging the data of both users John Doe overrides Max Mustermann.
        $this->assertEquals('John Doe', $user->property('name')->value());
    }
}
 
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 04.01.15 - 17:48
 */

namespace FileConnectorTest\Service;

use Application\SharedKernel\LocationTranslator;
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
use Ginger\Processor\RegistryWorkflowEngine;
use Ginger\Processor\Task\TaskListId;
use Ginger\Processor\Task\TaskListPosition;
use Ginger\Type\StringCollection;
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

    private $tempPath;

    /**
     * @var array
     */
    private $tempFiles = array();

    protected function setUp()
    {
        $this->messageReceiver = new StupidWorkflowProcessorMock();

        $this->commandBus = new CommandBus();

        $this->commandBus->utilize(new SingleTargetMessageRouter($this->messageReceiver));

        $this->commandBus->utilize(new WorkflowProcessorInvokeStrategy());

        $this->eventBus = new EventBus();

        $this->eventBus->utilize(new SingleTargetMessageRouter($this->messageReceiver));

        $this->eventBus->utilize(new WorkflowProcessorInvokeStrategy());

        $locationTranslator = new LocationTranslator([
            'temp' => sys_get_temp_dir(),
            'testdata' => $this->getTestDataPath()
        ]);

        $this->fileGateway = new FileGateway(
            Bootstrap::getServiceManager()->get('fileconnector.file_type_adapter_manager'),
            Bootstrap::getServiceManager()->get('fileconnector.filename_renderer'),
            $locationTranslator
        );

        $workflowEngine = new RegistryWorkflowEngine();

        $workflowEngine->registerCommandBus($this->commandBus, [NodeName::defaultName()->toString(), 'file-connector']);

        $workflowEngine->registerEventBus($this->eventBus, [NodeName::defaultName()->toString(), 'file-connector']);

        $this->fileGateway->useWorkflowEngine($workflowEngine);

        $this->tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
    }

    protected function tearDown()
    {
        $this->messageReceiver->reset();

        foreach($this->tempFiles as $tempFile) {
            $tempFile = $this->tempPath . $tempFile;
            if (file_exists($tempFile)) unlink($tempFile);
        }

        $this->tempFiles = [];
    }

    /**
     * @test
     */
    public function it_collects_users_from_a_single_csv_file()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $metadata = [
            //Use normal filename as pattern
            'filename_pattern' => 'users-header.csv',
            'path' => $this->getTestDataPath(),
            'file_type' => 'csv'
        ];

        $message = WorkflowMessage::collectDataOf(
            TestUserCollection::prototype(),
            NodeName::defaultName()->toString(),
            'file-connector',
            $metadata
        );

        $message->connectToProcessTask($taskListPosition);

        $this->fileGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $userCollection = $wfMessage->payload()->toType();

        $this->assertInstanceOf('FileConnectorTest\DataType\TestUserCollection', $userCollection);

        $this->assertEquals(3, count($userCollection->value()));
        $this->assertEquals(3, $wfMessage->metadata()['total_items']);

        foreach ($userCollection->value() as $testUser) {
            $this->assertInstanceOf('FileConnectorTest\DataType\TestUser', $testUser);
        }
    }

    /**
     * @test
     */
    public function it_collects_users_from_a_single_csv_file_using_location_instead_of_path()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $metadata = [
            //Use normal filename as pattern
            'filename_pattern' => 'users-header.csv',
            'location' => 'testdata',
            'file_type' => 'csv'
        ];

        $message = WorkflowMessage::collectDataOf(
            TestUserCollection::prototype(),
            NodeName::defaultName()->toString(),
            'file-connector',
            $metadata
        );

        $message->connectToProcessTask($taskListPosition);

        $this->fileGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $userCollection = $wfMessage->payload()->toType();

        $this->assertInstanceOf('FileConnectorTest\DataType\TestUserCollection', $userCollection);

        $this->assertEquals(3, count($userCollection->value()));
        $this->assertEquals(3, $wfMessage->metadata()['total_items']);

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
            //Use regex as filename
            'filename_pattern' => '/^testuser_.+\.json$/',
            'path' => $this->getTestDataPath(),
            'file_type' => 'json',
            'fetch_mode' => FileGateway::META_FETCH_MODE_MULTI_FILES,
            'file_data_type' => TestUser::prototype()->of(),
        ];

        $message = WorkflowMessage::collectDataOf(
            TestUserCollection::prototype(),
            NodeName::defaultName()->toString(),
            'file-connector',
            $metadata);

        $message->connectToProcessTask($taskListPosition);

        $this->fileGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $userCollection = $wfMessage->payload()->toType();

        $this->assertInstanceOf('FileConnectorTest\DataType\TestUserCollection', $userCollection);

        $this->assertEquals(2, count($userCollection->value()));
        $this->assertEquals(2, $wfMessage->metadata()['total_items']);

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
            'fetch_mode' => FileGateway::META_FETCH_MODE_MULTI_FILES,
            'merge_files' => true
        ];

        $message = WorkflowMessage::collectDataOf(
            TestUser::prototype(),
            NodeName::defaultName()->toString(),
            'file-connector',
            $metadata);

        $message->connectToProcessTask($taskListPosition);

        $this->fileGateway->handleWorkflowMessage($message);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        /** @var $wfMessage WorkflowMessage */
        $wfMessage = $this->messageReceiver->getLastReceivedMessage();

        $user = $wfMessage->payload()->toType();

        $this->assertInstanceOf('FileConnectorTest\DataType\TestUser', $user);

        //The FileGateway should have found both user files. It uses scandir with descending order so
        //testuser_max_mustermann.json should be handled before testuser_john_doe.json.
        //When merging the data of both users John Doe overrides Max Mustermann.
        $this->assertEquals('John Doe', $user->property('name')->value());
    }

    /**
     * @test
     */
    public function it_writes_users_to_file()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $testUsers = TestUserCollection::fromNativeValue([
            [
                "id" => 1,
                "name" => "John Doe",
                "age" => 34
            ],
            [
                "id" => 2,
                "name" => "Max Mustermann",
                "age" => 41
            ],
        ]);

        $metadata = [
            FileGateway::META_FILE_TYPE         => 'csv',
            FileGateway::META_PATH              => $this->tempPath,
            FileGateway::META_FILENAME_TEMPLATE => 'users-{{#now}}{{/now}}.csv',
        ];

        $this->tempFiles[] = 'users-' . date('Y-m-d') . '.csv';

        $workflowMessage = WorkflowMessage::newDataCollected(
            $testUsers,
            NodeName::defaultName()->toString(),
            'file-connector'
        );

        $workflowMessage->connectToProcessTask($taskListPosition);

        $workflowMessage = $workflowMessage->prepareDataProcessing($taskListPosition, NodeName::defaultName()->toString(), $metadata);

        $this->fileGateway->handleWorkflowMessage($workflowMessage);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        $this->assertTrue(file_exists($this->tempPath . $this->tempFiles[0]));
    }

    /**
     * @test
     */
    public function it_writes_users_to_file_using_location_instead_of_path()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $testUsers = TestUserCollection::fromNativeValue([
            [
                "id" => 1,
                "name" => "John Doe",
                "age" => 34
            ],
            [
                "id" => 2,
                "name" => "Max Mustermann",
                "age" => 41
            ],
        ]);

        $metadata = [
            FileGateway::META_FILE_TYPE         => 'csv',
            FileGateway::META_LOCATION          => 'temp',
            FileGateway::META_FILENAME_TEMPLATE => 'users-{{#now}}{{/now}}.csv',
        ];

        $this->tempFiles[] = 'users-' . date('Y-m-d') . '.csv';

        $workflowMessage = WorkflowMessage::newDataCollected(
            $testUsers,
            NodeName::defaultName()->toString(),
            'file-connector');

        $workflowMessage->connectToProcessTask($taskListPosition);

        $workflowMessage = $workflowMessage->prepareDataProcessing($taskListPosition, NodeName::defaultName()->toString(), $metadata);

        $this->fileGateway->handleWorkflowMessage($workflowMessage);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        $this->assertTrue(file_exists($this->tempPath . $this->tempFiles[0]));
    }

    /**
     * @test
     */
    public function it_writes_each_user_to_a_single_file_and_the_user_data_is_available_to_create_unique_file_names()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $testUsers = TestUserCollection::fromNativeValue([
            [
                "id" => 1,
                "name" => "John Doe",
                "age" => 34
            ],
            [
                "id" => 2,
                "name" => "Max Mustermann",
                "age" => 41
            ],
        ]);

        $metadata = [
            FileGateway::META_FILE_TYPE         => 'json',
            FileGateway::META_PATH              => $this->tempPath,
            FileGateway::META_FILENAME_TEMPLATE => 'user-{{data.id}}.json',
            FileGateway::META_WRITE_MULTI_FILES => true,
        ];

        $this->tempFiles[] = 'user-1.json';
        $this->tempFiles[] = 'user-2.json';

        $workflowMessage = WorkflowMessage::newDataCollected(
            $testUsers,
            NodeName::defaultName()->toString(),
            'file-connector'
        );

        $workflowMessage->connectToProcessTask($taskListPosition);

        $workflowMessage = $workflowMessage->prepareDataProcessing($taskListPosition, NodeName::defaultName()->toString(), $metadata);

        $this->fileGateway->handleWorkflowMessage($workflowMessage);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        $this->assertTrue(file_exists($this->tempPath . $this->tempFiles[0]));
        $this->assertTrue(file_exists($this->tempPath . $this->tempFiles[1]));

        $userData = json_decode(file_get_contents($this->tempPath . $this->tempFiles[0]), true);
        $user = TestUser::fromJsonDecodedData($userData);

        $this->assertEquals('John Doe', $user->property('name')->value());
    }

    /**
     * @test
     */
    public function it_writes_each_user_to_a_single_file_and_the_item_index_is_available_to_create_unique_file_names()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $testUsers = TestUserCollection::fromNativeValue([
            [
                "id" => 1,
                "name" => "John Doe",
                "age" => 34
            ],
            [
                "id" => 2,
                "name" => "Max Mustermann",
                "age" => 41
            ],
        ]);

        $metadata = [
            FileGateway::META_FILE_TYPE         => 'json',
            FileGateway::META_PATH              => $this->tempPath,
            FileGateway::META_FILENAME_TEMPLATE => 'user-{{item_index}}.json',
            FileGateway::META_WRITE_MULTI_FILES => true,
        ];

        $this->tempFiles[] = 'user-0.json';
        $this->tempFiles[] = 'user-1.json';

        $workflowMessage = WorkflowMessage::newDataCollected(
            $testUsers,
            NodeName::defaultName()->toString(),
            'file-connector');

        $workflowMessage->connectToProcessTask($taskListPosition);

        $workflowMessage = $workflowMessage->prepareDataProcessing($taskListPosition, NodeName::defaultName()->toString(), $metadata);

        $this->fileGateway->handleWorkflowMessage($workflowMessage);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        $this->assertTrue(file_exists($this->tempPath . $this->tempFiles[0]));
        $this->assertTrue(file_exists($this->tempPath . $this->tempFiles[1]));

        $userData = json_decode(file_get_contents($this->tempPath . $this->tempFiles[0]), true);
        $user = TestUser::fromJsonDecodedData($userData);

        $this->assertEquals('John Doe', $user->property('name')->value());
    }

    /**
     * @test
     */
    public function it_writes_each_string_of_the_collection_to_a_separate_file_and_the_value_is_available_in_the_filename_template_to_create_unique_file_names()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $strings = StringCollection::fromNativeValue([
            "first",
            "second",
            "third"
        ]);

        $metadata = [
            FileGateway::META_FILE_TYPE         => 'json',
            FileGateway::META_PATH              => $this->tempPath,
            FileGateway::META_FILENAME_TEMPLATE => 'string-{{value}}.json',
            FileGateway::META_WRITE_MULTI_FILES => true,
        ];

        $this->tempFiles[] = 'string-first.json';
        $this->tempFiles[] = 'string-second.json';
        $this->tempFiles[] = 'string-third.json';

        $workflowMessage = WorkflowMessage::newDataCollected(
            $strings,
            NodeName::defaultName()->toString(),
            'file-connector');

        $workflowMessage->connectToProcessTask($taskListPosition);

        $workflowMessage = $workflowMessage->prepareDataProcessing($taskListPosition, NodeName::defaultName()->toString(), $metadata);

        $this->fileGateway->handleWorkflowMessage($workflowMessage);

        $this->assertInstanceOf('Ginger\Message\WorkflowMessage', $this->messageReceiver->getLastReceivedMessage());

        $this->assertTrue(file_exists($this->tempPath . $this->tempFiles[0]));
        $this->assertTrue(file_exists($this->tempPath . $this->tempFiles[1]));
        $this->assertTrue(file_exists($this->tempPath . $this->tempFiles[2]));

        $second = json_decode(file_get_contents($this->tempPath . $this->tempFiles[1]));

        $this->assertEquals('second', $second);
    }
}
 
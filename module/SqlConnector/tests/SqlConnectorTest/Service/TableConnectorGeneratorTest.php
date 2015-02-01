<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/26/15 - 8:30 PM
 */
namespace SqlConnectorTest\Service;

use SqlConnectorTest\Mock\CommandBusMock;
use Application\SharedKernel\ApplicationDataTypeLocation;
use Application\SharedKernel\ConfigLocation;
use Application\SharedKernel\DataLocation;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;
use Ginger\Message\MessageNameUtils;
use SqlConnector\Service\DbalConnection;
use SqlConnector\Service\DbalConnectionCollection;
use SqlConnector\Service\SqlConnectorTranslator;
use SqlConnector\Service\TableConnectorGenerator;
use SqlConnectorTest\Bootstrap;
use SqlConnectorTest\TestCase;
use SystemConfig\Command\AddConnectorToConfig;

/**
 * Class TableConnectorGeneratorTest
 *
 * @package SqlConnectorTest\Service
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class TableConnectorGeneratorTest extends TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DataLocation
     */
    private $dataTypeLocation;

    /**
     * @var TableConnectorGenerator
     */
    private $tableConnectorGenerator;

    /**
     * @var CommandBusMock
     */
    private $commandBus;


    protected function setUp()
    {
        $this->connection = DbalConnection::fromConfiguration([
            'driver' => 'pdo_sqlite',
            'memory' => true,
            'dbname' => 'test_db',
        ]);

        $testDataTable = new Table("test_data");

        $testDataTable->addColumn("name", "string");
        $testDataTable->addColumn("age", "integer");
        $testDataTable->addColumn("created_at", "datetime");
        $testDataTable->addColumn("price", "float");
        $testDataTable->addColumn("active", "boolean");
        $testDataTable->setPrimaryKey(["name"]);

        $this->connection->connection()->getSchemaManager()->createTable($testDataTable);

        $this->dataTypeLocation = ApplicationDataTypeLocation::fromPath(sys_get_temp_dir());

        $this->commandBus = new CommandBusMock();

        if (! is_dir(sys_get_temp_dir() . "/SqlConnector")) {
            mkdir(sys_get_temp_dir() . "/SqlConnector");
            mkdir(sys_get_temp_dir() . "/SqlConnector/DataType");
        }

        $connections = new DbalConnectionCollection();

        $connections->add($this->connection);

        $this->tableConnectorGenerator = new TableConnectorGenerator(
            $connections,
            $this->dataTypeLocation,
            ConfigLocation::fromPath(sys_get_temp_dir()),
            $this->commandBus,
            Bootstrap::getServiceManager()->get("config")['sqlconnector']['doctrine_ginger_type_map']
        );
    }

    protected function tearDown()
    {
        @unlink(sys_get_temp_dir() . "/SqlConnector/TestDb/TestData.php");
        @unlink(sys_get_temp_dir() . "/SqlConnector/TestDb/TestDataCollection.php");
        @unlink(sys_get_temp_dir() . "/SqlConnector/TestDb");
        @unlink(sys_get_temp_dir() . "/SqlConnector");
        $this->commandBus->reset();
    }

    /**
     * @test
     */
    function it_adds_a_new_connector_and_generates_table_types()
    {
        $connectorData = [
            'dbal_connection' => 'test_db',
            'table' => 'test_data',
            'name' => 'Test Db Data',
        ];

        $this->tableConnectorGenerator->addConnector(SqlConnectorTranslator::generateConnectorId(), $connectorData);

        $expectedRowClassString = file_get_contents(getcwd() . "/SqlConnectorTest/Mock/TestData.php");
        $expectedCollectionClassString = file_get_contents(getcwd() . "/SqlConnectorTest/Mock/TestDataCollection.php");

        $pathToGeneratedRowFile = sys_get_temp_dir() . "/SqlConnector/TestDb/TestData.php";
        $pathToGeneratedCollectionFile = sys_get_temp_dir() . "/SqlConnector/TestDb/TestDataCollection.php";

        $generatedRowClassString = file_get_contents($pathToGeneratedRowFile);
        $generatedCollectionClassString = file_get_contents($pathToGeneratedCollectionFile);

        $this->assertEquals($expectedRowClassString, $generatedRowClassString);
        $this->assertEquals($expectedCollectionClassString, $generatedCollectionClassString);

        /** @var $lastMessage AddConnectorToConfig */
        $lastMessage = $this->commandBus->getLastMessage();

        $this->assertInstanceOf('SystemConfig\Command\AddConnectorToConfig', $lastMessage);

        $this->assertTrue(strpos($lastMessage->connectorId(), "sqlconnector:::") === 0);
        $this->assertEquals('Test Db Data', $lastMessage->connectorName());
        $this->assertEquals([MessageNameUtils::COLLECT_DATA, MessageNameUtils::PROCESS_DATA], $lastMessage->allowedMessage());
        $this->assertEquals([
            'Application\DataType\SqlConnector\TestDb\TestData',
            'Application\DataType\SqlConnector\TestDb\TestDataCollection'
        ], $lastMessage->allowedTypes());
        $this->assertEquals([
            'dbal_connection' => 'test_db',
            'table' => 'test_data',
            'icon' => TableConnectorGenerator::ICON,
            'ui_metadata_riot_tag' => TableConnectorGenerator::METADATA_UI_KEY,
        ], $lastMessage->additionalData());
    }
} 
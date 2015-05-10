<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;
use Prooph\Proophessor\Schema\EventStoreSchema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20150403204357 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        EventStoreSchema::createSchema($schema, 'link_process_manager_stream');

        $workflow = $schema->createTable('link_pm_read_workflow');

        $workflow->addColumn('id', 'string', ['length' => 36]);
        $workflow->addColumn('name', 'string', ['length' => 255]);
        $workflow->addColumn('node_name', 'string', ['length' => 255]);
        $workflow->addColumn('start_message', 'string', ['length' => 255, 'notnull' => false]);
        $workflow->addColumn('current_release', 'integer', ['default' => 0]);
        $workflow->addColumn('last_published_at', 'string', ['length' => 50, 'notnull' => false, 'default' => null]);
        $workflow->setPrimaryKey(['id']);

        $messageHandler = $schema->createTable('link_pm_read_message_handler');
        $messageHandler->addColumn('id', 'string', ['length' => 36]);
        $messageHandler->addColumn('name', 'string', ['length' => 255]);
        $messageHandler->addColumn('node_name', 'string', ['length' => 255]);
        $messageHandler->addColumn('type', 'string', ['length' => 10]);
        $messageHandler->addColumn('data_direction', 'string', ['length' => 20]);
        $messageHandler->addColumn('processing_types', 'text');
        $messageHandler->addColumn('processing_metadata', 'text');
        $messageHandler->addColumn('metadata_riot_tag', 'string', ['length' => 100]);
        $messageHandler->addColumn('icon', 'string', ['length' => 100]);
        $messageHandler->addColumn('icon_type', 'string', ['length' => 50]);
        $messageHandler->addColumn('preferred_type', 'string', ['length' => 255, 'notnull' => false]);
        $messageHandler->addColumn('processing_id', 'string', ['length' => 255, 'notnull' => false]);
        $messageHandler->addColumn('additional_data', 'text', ['notnull' => false]);
        $messageHandler->setPrimaryKey(['id']);

        $flowchartConfig = $schema->createTable('link_pm_flowchart_config');
        $flowchartConfig->addColumn('workflow_id', 'string', ['length' => 36]);
        $flowchartConfig->addColumn('config', 'text');
        $flowchartConfig->addColumn('last_updated_at', 'string', ['length' => 50]);
        $flowchartConfig->setPrimaryKey(['workflow_id']);

        $process = $schema->createTable('link_pm_read_process');
        $process->addColumn('id', 'string', ['length' => 36]);
        $process->addColumn('type', 'string', ['length' => 50]);
        $process->addColumn('workflow_id', 'string', ['length' => 36]);
        $process->setPrimaryKey(['id']);

        $task = $schema->createTable('link_pm_read_task');
        $task->addColumn('id', 'string', ['length' => 36]);
        $task->addColumn('type', 'string', ['length' => 50]);
        $task->addColumn('processing_type', 'string', ['length' => 255]);
        $task->addColumn('metadata', 'text');
        $task->addColumn('workflow_id', 'string', ['length' => 36]);
        $task->addColumn('process_id', 'string', ['length' => 36]);
        $task->addColumn('message_handler_id', 'string', ['length' => 36]);
        $task->setPrimaryKey(['id']);
    }

    public function down(Schema $schema)
    {
        EventStoreSchema::dropSchema($schema, 'link_process_manager_stream');
        $schema->dropTable('link_pm_read_workflow');
        $schema->dropTable('link_pm_read_message_handler');
        $schema->dropTable('link_pm_flowchart_config');
        $schema->dropTable('link_pm_read_process');
        $schema->dropTable('link_pm_read_task');
    }
}

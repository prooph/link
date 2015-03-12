<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20150312203643 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $processStream = $schema->createTable('process_stream');

        $processStream->addColumn('eventId', 'string', ['length' => 36]);
        $processStream->addColumn('version', 'integer');
        $processStream->addColumn('eventName', 'string', ['length' => 100]);
        $processStream->addColumn('payload', 'text');
        $processStream->addColumn('occurredOn', 'string', ['length' => 100]);
        $processStream->addColumn('aggregate_id', 'string', ['length' => 36]);
        $processStream->addColumn('aggregate_type', 'string', ['length' => 100]);
        $processStream->setPrimaryKey(['eventId']);
        $processStream->addUniqueIndex(['aggregate_id', 'aggregate_type', 'version'], 'metadata_version_uix');
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('process_stream');
    }
}

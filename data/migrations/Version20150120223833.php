<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20150120223833 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $processes = $schema->createTable("processes");

        $processes->addColumn('process_id', 'string', ['length' => 36]);
        $processes->addColumn('start_message', 'string', ['notnull' => false]);
        $processes->addColumn('status', 'string', ['length' => 10]);
        $processes->addColumn('started_at', 'string', ['notnull' => false]);
        $processes->addColumn('finished_at', 'string', ['notnull' => false]);
        $processes->setPrimaryKey(['process_id']);
        $processes->addIndex(['started_at'], 'started_at_idx');

    }

    public function down(Schema $schema)
    {
        $schema->dropTable('processes');
    }
}

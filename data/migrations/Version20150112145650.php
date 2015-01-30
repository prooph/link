<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20150112145650 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $messages = $schema->createTable("messages");

        $messages->addColumn('message_id', "string", ['length' => 36]);
        $messages->addColumn('message_name', "string", ['length' => 200]);
        $messages->addColumn('version', "integer");
        $messages->addColumn('logged_at', "string", ['length' => 100]);
        $messages->addColumn('task_list_position', "text", ['notnull' => false]);
        $messages->addColumn('process_id', "string", ['length' => 36, 'notnull' => false]);
        $messages->addColumn('status', "string", ['length' => 10]);
        $messages->addColumn('failure_msg', "text", ['notnull' => false]);

        $messages->setPrimaryKey(["message_id"]);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable("messages");
    }
}

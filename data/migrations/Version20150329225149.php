<?php

namespace Application\Migrations;

use Bernard\Doctrine\MessagesSchema;
use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20150329225149 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        MessagesSchema::create($schema);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('bernard_queues');
        $schema->dropTable('bernard_messages');
    }
}

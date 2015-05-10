<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;
use Prooph\Processing\Environment\Schema\EventStoreDoctrineSchema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20150312203643 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        EventStoreDoctrineSchema::createSchema($schema);
    }

    public function down(Schema $schema)
    {
        EventStoreDoctrineSchema::dropSchema($schema);
    }
}

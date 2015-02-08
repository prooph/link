<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 00:52
 */

namespace SqlConnectorTest\Fixture;

use Doctrine\DBAL\Connection;

/**
 * Class UsersFixture
 *
 * @package SqlConnectorTest\Fixture
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class UsersFixture
{
    public static function createTableAndInsertUsers(Connection $connection)
    {
        /*
         * Note: We make use of the automatic row id generation of Sqlite without defining the
         * id column as AUTOINCREMENT. If we would set it, we would not be able to test a "table truncate"
         * because AUTOINCREMENT tells Sqlite to not use a row id twice even if the row was deleted.
         * An empty table operation of the DoctrineTableGateway should result in an empty table with
         * row ids for new rows starting from 1.
         */
        $connection->exec('
            CREATE TABLE users
            (
                id INTEGER PRIMARY KEY NOT NULL,
                name TEXT NOT NULL,
                age INTEGER NOT NULL
            )
        ');

        $connection->insert('users', [
            'id' => 1,
            'name' => 'John Doe',
            'age'  => 34
        ]);

        $connection->insert('users', [
            'id' => 2,
            'name' => 'Max Mustermann',
            'age'  => 41
        ]);

        $connection->insert('users', [
            'id' => 3,
            'name' => 'Donald Duck',
            'age'  => 57
        ]);
    }

    public static function dropTable(Connection $connection)
    {
        $connection->exec('DROP TABLE users');
    }
}


 
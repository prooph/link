<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
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
        $connection->exec('
            CREATE TABLE users
            (
                id INT PRIMARY KEY NOT NULL,
                name TEXT NOT NULL,
                age INT NOT NULL
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


 
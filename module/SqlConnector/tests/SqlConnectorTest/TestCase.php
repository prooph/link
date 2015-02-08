<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 00:25
 */

namespace SqlConnectorTest;

use Doctrine\DBAL\Connection;

/**
 * Class TestCase
 *
 * @package SqlConnectorTest
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @return Connection
     */
    protected function getDbalConnection()
    {
        if (is_null($this->connection)) {
            $this->connection = \Doctrine\DBAL\DriverManager::getConnection(array(
                'driver' => 'pdo_sqlite',
                'memory' => true
            ));
        }

        return $this->connection;
    }
}
 
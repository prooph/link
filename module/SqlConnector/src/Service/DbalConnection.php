<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/26/15 - 9:54 PM
 */
namespace SqlConnector\Service;

use Assert\Assertion;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

/**
 * Class DbalConnection
 *
 * Wrapper for a doctrine dbal connection which groups the connection and its config in one object
 * and lazy loads the dbal connection
 *
 * @package SqlConnector\Service
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class DbalConnection 
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    private $supportedDrivers = [
        'pdo_mysql',
        'pdo_sqlite',
    ];

    /**
     * @param array $config
     * @return \SqlConnector\Service\DbalConnection
     */
    public static function fromConfiguration(array $config)
    {
        return new self($config);
    }

    /**
     * @param array $config
     */
    private function __construct(array $config)
    {
        $this->assertConfig($config);
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * @return Connection
     */
    public function connection()
    {
        if (is_null($this->connection)) {
            $this->connection = DriverManager::getConnection($this->config());
        }

        return $this->connection;
    }

    private function assertConfig(array $config)
    {
        Assertion::keyExists($config, "dbname");
        Assertion::string($config['dbname']);
        Assertion::keyExists($config, "driver");
        Assertion::inArray($config["driver"], $this->supportedDrivers);
    }
} 
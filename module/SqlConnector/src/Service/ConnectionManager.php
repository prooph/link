<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 25.01.15 - 22:23
 */

namespace SqlConnector\Service;
use Application\SharedKernel\ConfigLocation;
use SystemConfig\Model\ConfigWriter;

/**
 * Class ConnectionManager
 *
 * Stores dbal connection configs in <config_location>/sqlconnector.local.php
 *
 * @package SqlConnector\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ConnectionManager 
{
    const FILE_NAME = "sqlconnector.local.php";

    /**
     * @var ConfigLocation
     */
    private $configLocation;

    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var DbalConnectionCollection
     */
    private $connections;

    /**
     * @param ConfigLocation $configLocation
     * @param ConfigWriter $configWriter
     * @param DbalConnectionCollection $connections
     */
    public function __construct(ConfigLocation $configLocation, ConfigWriter $configWriter, DbalConnectionCollection $connections)
    {
        $this->configLocation = $configLocation;
        $this->configWriter = $configWriter;
        $this->connections = $connections;
    }

    /**
     * @param array $connection
     * @throws \InvalidArgumentException
     */
    public function addConnection(array $connection)
    {
        if ($this->connections->containsKey($connection['dbname'])) throw new \InvalidArgumentException(sprintf('A connection for DB %s already exists', $connection['dbname']));

        $this->connections->add(DbalConnection::fromConfiguration($connection));

        $this->saveConnections(true);
    }

    /**
     * @param array $connection
     * @throws \InvalidArgumentException
     */
    public function updateConnection(array $connection)
    {
        if (! $this->connections->containsKey($connection['dbname'])) throw new \InvalidArgumentException(sprintf('Connection for DB %s can not be found', $connection['dbname']));

        $this->connections->set($connection['dbname'], DbalConnection::fromConfiguration($connection));

        $this->saveConnections();
    }

    private function saveConnections($checkFile = false)
    {
        $path = $this->configLocation->toString() . DIRECTORY_SEPARATOR . self::FILE_NAME;

        $config = [
            'sqlconnector' => [
                'connections' => $this->connections->toArray()
            ]
        ];

        if ($checkFile && ! file_exists($path)) {
            $this->configWriter->writeNewConfigToDirectory($config, $path);
        } else {
            $this->configWriter->replaceConfigInDirectory($config, $path);
        }
    }
}
 
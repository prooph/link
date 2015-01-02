<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 01.01.15 - 23:16
 */

namespace SystemConfig\Model;

use Application\Event\RecordsSystemChangedEvents;
use Application\Event\SystemChangedEventRecorder;
use Application\SharedKernel\ConfigLocation;
use Application\SharedKernel\SqliteDbFile;
use SystemConfig\Event\EventStoreSetUpWasUndone;
use SystemConfig\Event\EventStoreWasInitialized;
use Zend\Stdlib\ErrorHandler;

/**
 * Class EventStoreConfig
 *
 * Manages event store configuration
 *
 * @package SystemConfig\Model
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class EventStoreConfig implements SystemChangedEventRecorder
{
    use RecordsSystemChangedEvents;

    /**
     * @var array
     */
    private $config = array();

    /**
     * @var ConfigLocation
     */
    private $configLocation;

    /**
     * Local config file name
     *
     * @var string
     */
    private static $configFileName = 'prooph.eventstore.local.php';

    /**
     * @param SqliteDbFile $sqliteDbFile
     * @param ConfigLocation $configLocation
     * @param ConfigWriter $configWriter
     * @return \SystemConfig\Model\EventStoreConfig
     */
    public static function initializeWithSqliteDb(SqliteDbFile $sqliteDbFile, ConfigLocation $configLocation, ConfigWriter $configWriter)
    {
        $config = [
            'prooph.event_store' => [
                'adapter' => [
                    'type' => 'Prooph\EventStore\Adapter\Doctrine\DoctrineEventStoreAdapter',
                    'options' => [
                        'connection' => [
                            'driver' => 'pdo_sqlite',
                            'path'   => $sqliteDbFile->toString()
                        ]
                    ]
                ]
            ]
        ];

        $instance = new self($config, $configLocation);

        $configWriter->writeNewConfigToDirectory($config, $configLocation->toString() . DIRECTORY_SEPARATOR . $instance->configFileName());

        $instance->recordThat(EventStoreWasInitialized::withSqliteDb($sqliteDbFile, $configLocation, $instance->configFileName()));

        return $instance;
    }

    /**
     * @param string $configLocation
     * @param string $sqliteDbFile
     * @return EventStoreSetUpWasUndone
     */
    public static function undoEventStoreSetUp($configLocation, $sqliteDbFile)
    {
        ErrorHandler::start();

        if (file_exists($configLocation . DIRECTORY_SEPARATOR . self::$configFileName))
            unlink($configLocation . DIRECTORY_SEPARATOR . self::$configFileName);

        if (file_exists($sqliteDbFile))
            unlink($sqliteDbFile);

        ErrorHandler::stop();

        return EventStoreSetUpWasUndone::in($configLocation . DIRECTORY_SEPARATOR . self::$configFileName);
    }

    /**
     * @param array $config
     * @param ConfigLocation $configLocation
     */
    private function __construct(array $config, ConfigLocation $configLocation)
    {
        $this->configLocation = $configLocation;
        $this->setConfig($config);
    }

    /**
     * @return string
     */
    public function configFileName()
    {
        return self::$configFileName;
    }

    /**
     * Returns array representation of the event store configuration
     *
     * @return array
     */
    public function toArray()
    {
        return $this->config;
    }

    /**
     * @param array $config
     * @throws \InvalidArgumentException
     */
    private function setConfig(array $config)
    {
        if (! array_key_exists('prooph.event_store', $config)) throw new \InvalidArgumentException('Missing the root key prooph.event_store in configuration');
        if (! is_array($config['prooph.event_store'])) throw new \InvalidArgumentException('prooph.event_store config must be an array');
        if (! array_key_exists('adapter', $config['prooph.event_store'])) throw new \InvalidArgumentException('Missing key adapter in prooph.event_store configuration');
        if (! is_array($config['prooph.event_store']['adapter'])) throw new \InvalidArgumentException('prooph.event_store adapter config must be an array');
        if (! array_key_exists('type', $config['prooph.event_store']['adapter'])) throw new \InvalidArgumentException('Missing key type in prooph.event_store adapter configuration');

        $this->config = $config;
    }
}
 
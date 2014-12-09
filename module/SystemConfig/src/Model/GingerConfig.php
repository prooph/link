<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 21:58
 */

namespace SystemConfig\Model;

use Application\Event\RecordsSystemChangedEvents;
use Application\Event\SystemChangedEventRecorder;
use Ginger\Environment\Environment;
use SystemConfig\Event\GingerConfigFileWasCreated;
use SystemConfig\Model\GingerConfig\ConfigLocation;

/**
 * Class GingerConfig
 *
 * This class handles ginger configuration manipulations. All changes should be done using this class because it
 * ensures validity of the desired change
 *
 * @package SystemConfig\Model
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class GingerConfig implements SystemChangedEventRecorder
{
    use RecordsSystemChangedEvents;

    /**
     * @var array
     */
    private $config = array();

    /**
     * Local config file name
     *
     * @var string
     */
    private static $configFileName = 'ginger.config.local.php';


    /**
     * Uses Ginger\Environment to initialize with its defaults
     */
    public static function initializeWithDefaultsIn(ConfigLocation $configLocation, ConfigWriter $configWriter)
    {
        $env = Environment::setUp();

        $instance = new self(['ginger' => $env->getConfig()->toArray()]);

        $configFilePath = $configLocation->toString() . DIRECTORY_SEPARATOR . self::$configFileName;

        if (file_exists($configFilePath)) {
            throw new \RuntimeException("Ginger config already exists: " . $configFilePath);
        }

        $configWriter->writeNewConfigToDirectory($instance->toArray(), $configFilePath);

        $instance->recordThat(GingerConfigFileWasCreated::in($configLocation, self::$configFileName));

        return $instance;
    }

    /**
     * @param string $path
     * @return GingerConfig
     * @throws \InvalidArgumentException
     */
    public static function initializeFromConfigLocation($path)
    {
        if (! file_exists((string)$path . '/' . self::$configFileName)) {
            throw new \InvalidArgumentException(sprintf('The ginger config file %s can not be found in location %s', self::$configFileName, (string)$path));
        }

        $config = include ((string)$path . '/' . self::$configFileName);

        if (! is_array($config)) {
            throw new \InvalidArgumentException(sprintf('Config file %s does not return a valid config array', (string)$path . '/' . self::$configFileName));
        }

        return new self($config);
    }

    /**
     * @param array $config
     */
    private function __construct(array $config)
    {
        $this->setConfig($config);
    }

    /**
     * Returns array representation of the Ginger configuration
     *
     * @return array
     */
    public function toArray()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function configFileName()
    {
        return self::$configFileName;
    }

    public function changeNodeName($newNodeName)
    {

    }

    /**
     * Assert and set config
     *
     * @param array $config
     * @throws \InvalidArgumentException
     */
    private function setConfig(array $config)
    {
        if (! array_key_exists('ginger', $config)) {
            throw new \InvalidArgumentException('Missing the root key ginger in configuration');
        }
        $this->config = $config;
    }
}
 
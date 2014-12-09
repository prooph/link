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
use Ginger\Processor\NodeName;
use SystemConfig\Event\GingerConfigFileWasCreated;
use Application\SharedKernel\ConfigLocation;
use SystemConfig\Event\NodeNameWasChanged;

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
     * @var ConfigLocation
     */
    private $configLocation;

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

        $instance = new self(['ginger' => $env->getConfig()->toArray()], $configLocation);

        $configFilePath = $configLocation->toString() . DIRECTORY_SEPARATOR . self::$configFileName;

        if (file_exists($configFilePath)) {
            throw new \RuntimeException("Ginger config already exists: " . $configFilePath);
        }

        $configWriter->writeNewConfigToDirectory($instance->toArray(), $configFilePath);

        $instance->configLocation = $configLocation;

        $instance->recordThat(GingerConfigFileWasCreated::in($configLocation, self::$configFileName));

        return $instance;
    }

    /**
     * @param ConfigLocation $configLocation
     * @throws \InvalidArgumentException
     * @return GingerConfig
     */
    public static function initializeFromConfigLocation(ConfigLocation $configLocation)
    {
        return new self($configLocation->getConfigArray(self::$configFileName), $configLocation);
    }

    /**
     * @param ConfigLocation $configLocation
     * @return \SystemConfig\Projection\GingerConfig
     */
    public static function asProjectionFrom(ConfigLocation $configLocation)
    {
        if (file_exists($configLocation->toString() . DIRECTORY_SEPARATOR . self::$configFileName)) {
            return new \SystemConfig\Projection\GingerConfig($configLocation->getConfigArray(self::$configFileName), true);
        } else {
            $env = Environment::setUp();

            return new \SystemConfig\Projection\GingerConfig(['ginger' => $env->getConfig()]);
        }
    }

    /**
     * @param array $config
     * @param ConfigLocation $configLocation
     */
    private function __construct(array $config, ConfigLocation $configLocation)
    {
        $this->setConfig($config);
        $this->configLocation = $configLocation;
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
    public static function configFileName()
    {
        return self::$configFileName;
    }

    /**
     * @param NodeName $newNodeName
     * @param ConfigWriter $configWriter
     */
    public function changeNodeName(NodeName $newNodeName, ConfigWriter $configWriter)
    {
        $oldNodeName = NodeName::fromString($this->config['ginger']['node_name']);

        $this->config['ginger']['node_name'] = $newNodeName->toString();

        foreach ($this->config['ginger']['buses']['workflow_processor_command_bus']['targets'] as $i => $target) {
            if ($target === $oldNodeName->toString()) {
                $this->config['ginger']['buses']['workflow_processor_command_bus']['targets'][$i] = $newNodeName->toString();
            }
        }

        foreach ($this->config['ginger']['buses']['workflow_processor_event_bus']['targets'] as $i => $target) {
            if ($target === $oldNodeName->toString()) {
                $this->config['ginger']['buses']['workflow_processor_event_bus']['targets'][$i] = $newNodeName->toString();
            }
        }

        $configWriter->replaceConfigInDirectory(
            $this->toArray(),
            $this->configLocation->toString() . DIRECTORY_SEPARATOR . self::$configFileName
        );

        $this->recordThat(NodeNameWasChanged::to($newNodeName, $oldNodeName));
    }

    /**
     * Assert and set config
     *
     * @param array $config
     * @throws \InvalidArgumentException
     */
    private function setConfig(array $config)
    {
        if (! array_key_exists('ginger', $config))                                 throw new \InvalidArgumentException('Missing the root key ginger in configuration');
        if (! array_key_exists('node_name', $config['ginger']))                    throw new \InvalidArgumentException('Missing key node_name in ginger config');
        if (! array_key_exists('plugins', $config['ginger']))                      throw new \InvalidArgumentException('Missing key plugins in ginger config');
        if (! is_array($config['ginger']['plugins']))                              throw new \InvalidArgumentException('Plugins must be an array in ginger config');
        if (! array_key_exists('buses', $config['ginger']))                        throw new \InvalidArgumentException('Missing key buses in ginger config');
        if (! is_array($config['ginger']['buses']))                                throw new \InvalidArgumentException('Buses must be an array in ginger config');
        if (! isset($config['ginger']['buses']['workflow_processor_command_bus'])) throw new \InvalidArgumentException('Missing workflow_processor_command_bus in config ginger.buses');
        if (! isset($config['ginger']['buses']['workflow_processor_event_bus']))   throw new \InvalidArgumentException('Missing workflow_processor_event_bus in config ginger.buses');
        if (! array_key_exists('processes', $config['ginger']))                    throw new \InvalidArgumentException('Missing key processes in ginger config');
        if (! is_array($config['ginger']['processes']))                            throw new \InvalidArgumentException('Processes must be an array in ginger config');

        $this->assertBusConfig($config['ginger']['buses']['workflow_processor_command_bus'], 'workflow_processor_command_bus');
        $this->assertBusConfig($config['ginger']['buses']['workflow_processor_event_bus'], 'workflow_processor_event_bus');

        $this->config = $config;
    }

    /**
     * @param array $config
     * @param string $busName
     * @throws \InvalidArgumentException
     */
    private function assertBusConfig(array $config, $busName)
    {
        if (! array_key_exists('type', $config))    throw new \InvalidArgumentException('Missing key type in config ginger.buses.'.$busName);
        if (! array_key_exists('targets', $config)) throw new \InvalidArgumentException('Missing key targets in config ginger.buses.'.$busName);
        if (! array_key_exists('utils', $config))   throw new \InvalidArgumentException('Missing key utils in config ginger.buses.'.$busName);
        if (! is_array($config['targets']))         throw new \InvalidArgumentException(sprintf('Config ginger.buses.%s.targets must be an array', $busName));
        if (! is_array($config['targets']))         throw new \InvalidArgumentException(sprintf('Config ginger.buses.%s.utils must be an array', $busName));
    }
}
 
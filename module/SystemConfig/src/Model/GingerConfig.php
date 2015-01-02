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
use Application\SharedKernel\DataTypeClass;
use Ginger\Environment\Environment;
use Ginger\Message\MessageNameUtils;
use Ginger\Processor\Definition;
use Ginger\Processor\NodeName;
use SystemConfig\Event\GingerConfigFileWasCreated;
use Application\SharedKernel\ConfigLocation;
use SystemConfig\Event\GingerConfigFileWasRemoved;
use SystemConfig\Event\NewProcessWasAddedToConfig;
use SystemConfig\Event\NodeNameWasChanged;
use Zend\Stdlib\ErrorHandler;

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
     * @var \SystemConfig\Projection\GingerConfig
     */
    private $projection;

    /**
     * Uses Ginger\Environment to initialize with its defaults
     */
    public static function initializeWithDefaultsIn(ConfigLocation $configLocation, ConfigWriter $configWriter)
    {
        $env = Environment::setUp();

        $instance = new self(['ginger' => array_merge($env->getConfig()->toArray(), ["connectors" => []])], $configLocation);

        $configFilePath = $configLocation->toString() . DIRECTORY_SEPARATOR . self::$configFileName;

        if (file_exists($configFilePath)) {
            throw new \RuntimeException("Ginger config already exists: " . $configFilePath);
        }

        $configWriter->writeNewConfigToDirectory($instance->toArray(), $configFilePath);

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
            $instance = self::initializeFromConfigLocation($configLocation);
            return new \SystemConfig\Projection\GingerConfig($instance->toArray(), $configLocation, true);
        } else {
            $env = Environment::setUp();

            return new \SystemConfig\Projection\GingerConfig(['ginger' => $env->getConfig()], $configLocation);
        }
    }

    /**
     * @param string $configLocation
     * @return GingerConfigFileWasRemoved
     */
    public static function removeConfig($configLocation)
    {
        ErrorHandler::start();

        if (file_exists($configLocation . DIRECTORY_SEPARATOR . self::$configFileName))
            unlink($configLocation . DIRECTORY_SEPARATOR . self::$configFileName);

        ErrorHandler::stop();

        return GingerConfigFileWasRemoved::in($configLocation . DIRECTORY_SEPARATOR . self::$configFileName);
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

        foreach ($this->config['ginger']['channels']['local']['targets'] as $i => $target) {
            if ($target === $oldNodeName->toString()) {
                $this->config['ginger']['channels']['local']['targets'][$i] = $newNodeName->toString();
            }
        }

        $configWriter->replaceConfigInDirectory(
            $this->toArray(),
            $this->configLocation->toString() . DIRECTORY_SEPARATOR . self::$configFileName
        );

        $this->recordThat(NodeNameWasChanged::to($newNodeName, $oldNodeName));
    }

    /**
     * @param string $name
     * @param string $type
     * @param string $startMessage
     * @param array $tasks
     * @param ConfigWriter $configWriter
     */
    public function addProcess($name, $type, $startMessage, array $tasks, ConfigWriter $configWriter)
    {
        $processConfig = ["name" => $name, "process_type" => $type, "tasks" => $tasks];

        $this->assertMessageName($startMessage, $this->projection()->getAllPossibleDataTypes());
        $this->assertStartMessageNotExists($startMessage);
        $this->assertProcessConfig($startMessage, $processConfig);

        $this->config['ginger']['processes'][$startMessage] = $processConfig;

        $configWriter->replaceConfigInDirectory(
            $this->toArray(),
            $this->configLocation->toString() . DIRECTORY_SEPARATOR . self::$configFileName
        );

        $this->recordThat(NewProcessWasAddedToConfig::withDefinition($startMessage, $processConfig));
    }

    /**
     * @param string $startMessage
     * @param array $processConfig
     * @param ConfigWriter $configWriter
     * @throws \InvalidArgumentException
     */
    public function replaceProcessTriggeredBy($startMessage, array $processConfig, ConfigWriter $configWriter)
    {
        $this->assertMessageName($startMessage, $this->projection()->getAllPossibleDataTypes());
        $this->assertProcessConfig($startMessage, $processConfig);

        if (! isset($this->config['ginger']['processes'][$startMessage])) throw new \InvalidArgumentException(sprintf('Can not find a process that is triggered by message %s', $startMessage));

        $oldProcessConfig = $this->config['ginger']['processes'][$startMessage];

        $this->config['ginger']['processes'][$startMessage] = $processConfig;

        $configWriter->replaceConfigInDirectory(
            $this->toArray(),
            $this->configLocation->toString() . DIRECTORY_SEPARATOR . self::$configFileName
        );
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
        if (! array_key_exists('connectors', $config['ginger']))                   throw new \InvalidArgumentException('Missing key connectors in ginger config');
        if (! is_array($config['ginger']['connectors']))                           throw new \InvalidArgumentException('Connectors must be an array in ginger config');
        if (! array_key_exists('channels', $config['ginger']))                     throw new \InvalidArgumentException('Missing key channels in ginger config');
        if (! is_array($config['ginger']['channels']))                             throw new \InvalidArgumentException('Channels must be an array in ginger config');
        if (! isset($config['ginger']['channels']['local']))                       throw new \InvalidArgumentException('Missing local channel config in ginger.channels');
        if (! array_key_exists('processes', $config['ginger']))                    throw new \InvalidArgumentException('Missing key processes in ginger config');
        if (! is_array($config['ginger']['processes']))                            throw new \InvalidArgumentException('Processes must be an array in ginger config');

        $this->assertChannelConfig($config['ginger']['channels']['local'], 'local');

        $projection = new \SystemConfig\Projection\GingerConfig($config, $this->configLocation, true);

        foreach ($config['ginger']['processes'] as $startMessage => $processConfig) {
            $this->assertMessageName($startMessage, $projection->getAllPossibleDataTypes());
            $this->assertProcessConfig($startMessage, $processConfig);
        }

        $this->config = $config;
    }

    /**
     * @return \SystemConfig\Projection\GingerConfig
     */
    private function projection()
    {
        if (is_null($this->projection)) {
            $this->projection = new \SystemConfig\Projection\GingerConfig($this->toArray(), $this->configLocation,  true);
        }

        return $this->projection;
    }

    /**
     * @param array $config
     * @param string $channelName
     * @throws \InvalidArgumentException
     */
    private function assertChannelConfig(array $config, $channelName)
    {
        if (! array_key_exists('targets', $config)) throw new \InvalidArgumentException('Missing key targets in config ginger.channels.'.$channelName);
        if (! array_key_exists('utils', $config))   throw new \InvalidArgumentException('Missing key utils in config ginger.channels.'.$channelName);
        if (! is_array($config['targets']))         throw new \InvalidArgumentException(sprintf('Config ginger.channels.%s.targets must be an array', $channelName));
    }

    /**
     * @param string $messageName
     * @param array $possibleGingerTypes
     * @throws \InvalidArgumentException
     */
    private function assertMessageName($messageName, array $possibleGingerTypes)
    {
        if (! is_string($messageName)) throw new \InvalidArgumentException('Message name must be a string');

        if (! MessageNameUtils::isGingerMessage($messageName)) throw new \InvalidArgumentException(sprintf(
            'Message name format -%s- is not valid',
            $messageName
        ));



        DataTypeClass::extractFromMessageName($messageName, $possibleGingerTypes);
    }

    /**
     * @param string $messageName
     * @throws \InvalidArgumentException
     */
    private function assertStartMessageNotExists($messageName)
    {
        $messageNames = array_keys($this->projection()->getProcessDefinitions());

        if (in_array($messageName, $messageNames)) throw new \InvalidArgumentException(sprintf(
            "Message name %s is already defined as start message",
            $messageName
        ));
    }

    /**
     * @param string $messageName
     * @param array $processConfig
     * @throws \InvalidArgumentException
     */
    private function assertProcessConfig($messageName, array $processConfig)
    {
        if (! array_key_exists('name', $processConfig))         throw new \InvalidArgumentException('Missing key name in config ginger.processes.'.$messageName);
        if (! array_key_exists('process_type', $processConfig)) throw new \InvalidArgumentException('Missing key process_type in config ginger.processes.'.$messageName);
        if (! in_array($processConfig['process_type'], Definition::getAllProcessTypes())) throw new \InvalidArgumentException('Process type is not valid in config ginger.processes.'.$messageName);
        if (! array_key_exists('tasks', $processConfig))        throw new \InvalidArgumentException('Missing key tasks in config ginger.processes.'.$messageName);
        if (! is_array($processConfig['tasks']))                throw new \InvalidArgumentException(sprintf('Config ginger.processes.%s.tasks must be an array', $messageName));

        foreach ($processConfig['tasks'] as $i => $task) $this->assertTaskConfig($messageName, $i, $task);
    }

    private function assertTaskConfig($messageName, $taskIndex, array $taskConfig)
    {
        $configPath = 'ginger.processes.' . $messageName . '.tasks.' .$taskIndex;

        if (! array_key_exists('task_type', $taskConfig))       throw new \InvalidArgumentException('Missing task type in config ' . $configPath);

        switch ($taskConfig['task_type']) {
            case Definition::TASK_COLLECT_DATA:
                if (! array_key_exists('source', $taskConfig))              throw new \InvalidArgumentException('Missing source in config '. $configPath);
                if (! array_key_exists('data_type', $taskConfig))           throw new \InvalidArgumentException('Missing data type in config '. $configPath);
                break;
            case Definition::TASK_PROCESS_DATA:
                if (! array_key_exists('target', $taskConfig))              throw new \InvalidArgumentException('Missing target in config '. $configPath);
                if (! array_key_exists('allowed_types', $taskConfig))       throw new \InvalidArgumentException('Missing allowed types in config '. $configPath);
                break;
            case Definition::TASK_MANIPULATE_PAYLOAD:
                if (! array_key_exists('manipulation_script', $taskConfig)) throw new \InvalidArgumentException('Missing manipulation script in config '. $configPath);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid task type %s in config %s', $taskConfig['task_type'], $configPath));
        }
    }
}
 
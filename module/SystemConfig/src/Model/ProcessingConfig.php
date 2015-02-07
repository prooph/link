<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 21:58
 */

namespace SystemConfig\Model;

use Application\Event\RecordsSystemChangedEvents;
use Application\Event\SystemChangedEventRecorder;
use Application\SharedKernel\ProcessingTypeClass;
use Prooph\Processing\Environment\Environment;
use Prooph\Processing\Message\MessageNameUtils;
use Prooph\Processing\Processor\Definition;
use Prooph\Processing\Processor\NodeName;
use SystemConfig\Event\ConnectorConfigWasChanged;
use SystemConfig\Event\ConnectorWasAddedToConfig;
use SystemConfig\Event\ProcessingConfigFileWasCreated;
use Application\SharedKernel\ConfigLocation;
use SystemConfig\Event\ProcessingConfigFileWasRemoved;
use SystemConfig\Event\NewProcessWasAddedToConfig;
use SystemConfig\Event\NodeNameWasChanged;
use SystemConfig\Event\ProcessConfigWasChanged;
use Zend\Stdlib\ErrorHandler;

/**
 * Class ProcessingConfig
 *
 * This class handles processing configuration manipulations. All changes should be done using this class because it
 * ensures validity of the desired change
 *
 * @package SystemConfig\Model
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ProcessingConfig implements SystemChangedEventRecorder
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
    private static $configFileName = 'processing.config.local.php';

    /**
     * @var \SystemConfig\Projection\ProcessingConfig
     */
    private $projection;

    /**
     * @var array
     */
    private $availableMessageTypes;

    /**
     * Uses Prooph\Processing\Environment to initialize with its defaults
     */
    public static function initializeWithDefaultsIn(ConfigLocation $configLocation, ConfigWriter $configWriter)
    {
        $env = Environment::setUp();

        $instance = new self(['processing' => array_merge($env->getConfig()->toArray(), ["connectors" => []])], $configLocation);

        $configFilePath = $configLocation->toString() . DIRECTORY_SEPARATOR . self::$configFileName;

        if (file_exists($configFilePath)) {
            throw new \RuntimeException("Processing config already exists: " . $configFilePath);
        }

        $configWriter->writeNewConfigToDirectory($instance->toArray(), $configFilePath);

        $instance->recordThat(ProcessingConfigFileWasCreated::in($configLocation, self::$configFileName));

        return $instance;
    }

    /**
     * @param ConfigLocation $configLocation
     * @throws \InvalidArgumentException
     * @return ProcessingConfig
     */
    public static function initializeFromConfigLocation(ConfigLocation $configLocation)
    {
        return new self($configLocation->getConfigArray(self::$configFileName), $configLocation);
    }

    /**
     * @param ConfigLocation $configLocation
     * @return \SystemConfig\Projection\ProcessingConfig
     */
    public static function asProjectionFrom(ConfigLocation $configLocation)
    {
        if (file_exists($configLocation->toString() . DIRECTORY_SEPARATOR . self::$configFileName)) {
            $instance = self::initializeFromConfigLocation($configLocation);
            return new \SystemConfig\Projection\ProcessingConfig($instance->toArray(), $configLocation, true);
        } else {
            $env = Environment::setUp();

            return new \SystemConfig\Projection\ProcessingConfig(['processing' => $env->getConfig()->toArray()], $configLocation);
        }
    }

    /**
     * @param string $configLocation
     * @return ProcessingConfigFileWasRemoved
     */
    public static function removeConfig($configLocation)
    {
        ErrorHandler::start();

        if (file_exists($configLocation . DIRECTORY_SEPARATOR . self::$configFileName))
            unlink($configLocation . DIRECTORY_SEPARATOR . self::$configFileName);

        ErrorHandler::stop();

        return ProcessingConfigFileWasRemoved::in($configLocation . DIRECTORY_SEPARATOR . self::$configFileName);
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
     * Returns array representation of the processing configuration
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
        $oldNodeName = NodeName::fromString($this->config['processing']['node_name']);

        $this->config['processing']['node_name'] = $newNodeName->toString();

        foreach ($this->config['processing']['channels']['local']['targets'] as $i => $target) {
            if ($target === $oldNodeName->toString()) {
                $this->config['processing']['channels']['local']['targets'][$i] = $newNodeName->toString();
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

        $this->assertMessageName($startMessage, $this->projection()->getAllAvailableProcessingTypes());
        $this->assertStartMessageNotExists($startMessage);
        $this->assertProcessConfig($startMessage, $processConfig);

        $this->config['processing']['processes'][$startMessage] = $processConfig;

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
        $this->assertMessageName($startMessage, $this->projection()->getAllAvailableProcessingTypes());
        $this->assertProcessConfig($startMessage, $processConfig);

        if (! isset($this->config['processing']['processes'][$startMessage])) throw new \InvalidArgumentException(sprintf('Can not find a process that is triggered by message %s', $startMessage));

        $oldProcessConfig = $this->config['processing']['processes'][$startMessage];

        $this->config['processing']['processes'][$startMessage] = $processConfig;

        $configWriter->replaceConfigInDirectory(
            $this->toArray(),
            $this->configLocation->toString() . DIRECTORY_SEPARATOR . self::$configFileName
        );

        $this->recordThat(ProcessConfigWasChanged::to($processConfig, $oldProcessConfig, $startMessage));
    }

    /**
     * @param string $connectorId
     * @param string $connectorName
     * @param array $allowedMessages
     * @param array $allowedTypes
     * @param ConfigWriter $configWriter
     * @param array $additionConfig
     * @throws \InvalidArgumentException
     */
    public function addConnector($connectorId, $connectorName, array $allowedMessages, array $allowedTypes, ConfigWriter $configWriter, array $additionConfig = array())
    {
        $connectorConfig = array_merge([
            'name' => $connectorName,
            'allowed_messages' => $allowedMessages,
            'allowed_types' => $allowedTypes,
        ], $additionConfig);

        $this->assertConnectorConfig($connectorId, $connectorConfig, $this->projection(), true);

        if (in_array($connectorId, $this->projection()->getConnectors())) throw new \InvalidArgumentException(sprintf("A connector with id %s is already configured", $connectorId));

        $this->config['processing']['connectors'][$connectorId] = $connectorConfig;

        $configWriter->replaceConfigInDirectory(
            $this->toArray(),
            $this->configLocation->toString() . DIRECTORY_SEPARATOR . self::$configFileName
        );

        $this->recordThat(ConnectorWasAddedToConfig::withDefinition($connectorId, $connectorConfig));
    }

    /**
     * @param string $connectorId
     * @param array $connectorConfig
     * @param ConfigWriter $configWriter
     * @throws \InvalidArgumentException
     */
    public function changeConnector($connectorId, array $connectorConfig, ConfigWriter $configWriter)
    {
        if (! isset($this->config['processing']['connectors'][$connectorId])) throw new \InvalidArgumentException(sprintf('Connector with id %s can not be found', $connectorId));

        $this->assertConnectorConfig($connectorId, $connectorConfig, $this->projection(), false);

        $oldConfig = $this->config['processing']['connectors'][$connectorId];

        $this->config['processing']['connectors'][$connectorId] = $connectorConfig;

        $configWriter->replaceConfigInDirectory(
            $this->toArray(),
            $this->configLocation->toString() . DIRECTORY_SEPARATOR . self::$configFileName
        );

        $this->recordThat(ConnectorConfigWasChanged::to($connectorConfig, $oldConfig, $connectorId));
    }

    /**
     * Assert and set config
     *
     * @param array $config
     * @throws \InvalidArgumentException
     */
    private function setConfig(array $config)
    {
        if (! array_key_exists('processing', $config))                                 throw new \InvalidArgumentException('Missing the root key processing in configuration');
        if (! array_key_exists('node_name', $config['processing']))                    throw new \InvalidArgumentException('Missing key node_name in processing config');
        if (! array_key_exists('plugins', $config['processing']))                      throw new \InvalidArgumentException('Missing key plugins in processing config');
        if (! is_array($config['processing']['plugins']))                              throw new \InvalidArgumentException('Plugins must be an array in processing config');
        if (! array_key_exists('connectors', $config['processing']))                   throw new \InvalidArgumentException('Missing key connectors in processing config');
        if (! is_array($config['processing']['connectors']))                           throw new \InvalidArgumentException('Connectors must be an array in processing config');
        if (! array_key_exists('channels', $config['processing']))                     throw new \InvalidArgumentException('Missing key channels in processing config');
        if (! is_array($config['processing']['channels']))                             throw new \InvalidArgumentException('Channels must be an array in processing config');
        if (! isset($config['processing']['channels']['local']))                       throw new \InvalidArgumentException('Missing local channel config in processing.channels');
        if (! array_key_exists('processes', $config['processing']))                    throw new \InvalidArgumentException('Missing key processes in processing config');
        if (! is_array($config['processing']['processes']))                            throw new \InvalidArgumentException('Processes must be an array in processing config');

        $this->assertChannelConfig($config['processing']['channels']['local'], 'local');

        $projection = new \SystemConfig\Projection\ProcessingConfig($config, $this->configLocation, true);

        foreach ($config['processing']['processes'] as $startMessage => $processConfig) {
            $this->assertMessageName($startMessage, $projection->getAllAvailableProcessingTypes());
            $this->assertProcessConfig($startMessage, $processConfig);
        }

        foreach ($config['processing']['connectors'] as $connectorId => $connectorConfig)
        {
            if (! is_array($connectorConfig)) throw new \InvalidArgumentException(sprintf('Connector config for connector %s must be an array', $connectorId));
            $this->assertConnectorConfig($connectorId, $connectorConfig, $projection, false);
        }

        $this->config = $config;
    }

    /**
     * @return \SystemConfig\Projection\ProcessingConfig
     */
    private function projection()
    {
        if (is_null($this->projection)) {
            $this->projection = new \SystemConfig\Projection\ProcessingConfig($this->toArray(), $this->configLocation,  true);
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
        if (! array_key_exists('targets', $config)) throw new \InvalidArgumentException('Missing key targets in config processing.channels.'.$channelName);
        if (! array_key_exists('utils', $config))   throw new \InvalidArgumentException('Missing key utils in config processing.channels.'.$channelName);
        if (! is_array($config['targets']))         throw new \InvalidArgumentException(sprintf('Config processing.channels.%s.targets must be an array', $channelName));
    }

    /**
     * @param string $messageName
     * @param array $availableProcessingTypes
     * @throws \InvalidArgumentException
     */
    private function assertMessageName($messageName, array $availableProcessingTypes)
    {
        if (! is_string($messageName)) throw new \InvalidArgumentException('Message name must be a string');

        if (! MessageNameUtils::isWorkflowMessage($messageName)) throw new \InvalidArgumentException(sprintf(
            'Message name format -%s- is not valid',
            $messageName
        ));



        ProcessingTypeClass::extractFromMessageName($messageName, $availableProcessingTypes);
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
        if (! array_key_exists('name', $processConfig))         throw new \InvalidArgumentException('Missing key name in config processing.processes.'.$messageName);
        if (! array_key_exists('process_type', $processConfig)) throw new \InvalidArgumentException('Missing key process_type in config processing.processes.'.$messageName);
        if (! in_array($processConfig['process_type'], Definition::getAllProcessTypes())) throw new \InvalidArgumentException('Process type is not valid in config processing.processes.'.$messageName);
        if (! array_key_exists('tasks', $processConfig))        throw new \InvalidArgumentException('Missing key tasks in config processing.processes.'.$messageName);
        if (! is_array($processConfig['tasks']))                throw new \InvalidArgumentException(sprintf('Config processing.processes.%s.tasks must be an array', $messageName));

        foreach ($processConfig['tasks'] as $i => $task) $this->assertTaskConfig($messageName, $i, $task);
    }

    private function assertTaskConfig($messageName, $taskIndex, array $taskConfig)
    {
        $configPath = 'processing.processes.' . $messageName . '.tasks.' .$taskIndex;

        if (! array_key_exists('task_type', $taskConfig))       throw new \InvalidArgumentException('Missing task type in config ' . $configPath);

        switch ($taskConfig['task_type']) {
            case Definition::TASK_COLLECT_DATA:
                if (! array_key_exists('source', $taskConfig))              throw new \InvalidArgumentException('Missing source in config '. $configPath);
                if (! array_key_exists('processing_type', $taskConfig))           throw new \InvalidArgumentException('Missing data type in config '. $configPath);
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

    /**
     * @param string $connectorId
     * @param array $connectorConfig
     * @param \SystemConfig\Projection\ProcessingConfig $config
     * @param bool $isNewConnector
     * @throws \InvalidArgumentException
     */
    private function assertConnectorConfig($connectorId, array $connectorConfig, \SystemConfig\Projection\ProcessingConfig $config, $isNewConnector = false)
    {
        if (! is_string($connectorId) || empty($connectorId)) throw new \InvalidArgumentException("Connector id must a non empty string");

        if (! array_key_exists('name', $connectorConfig))       throw new \InvalidArgumentException('Missing name in connector config '. $connectorId);
        if (!is_string($connectorConfig['name']) || empty($connectorConfig['name'])) throw new \InvalidArgumentException('Name must be a non empty string in connector config '. $connectorId);
        if (! array_key_exists('allowed_messages', $connectorConfig))       throw new \InvalidArgumentException('Missing allowed messages in connector config '. $connectorId);
        if (!is_array($connectorConfig['allowed_messages'])) throw new \InvalidArgumentException('Allowed messages must be an array in connector config '. $connectorId);

        array_walk($connectorConfig['allowed_messages'], function($allowedMessage) use ($connectorId) {
            if (! in_array($allowedMessage, $this->getAvailableMessageTypes())) throw new \InvalidArgumentException(sprintf('Allowed message %s is not a valid workflow message suffix in connector config %s', $allowedMessage, $connectorId));
        });

        if (! array_key_exists('allowed_types', $connectorConfig))       throw new \InvalidArgumentException('Missing allowed types in connector config '. $connectorId);
        if (!is_array($connectorConfig['allowed_types'])) throw new \InvalidArgumentException('Allowed types must be an array in connector config '. $connectorId);

        if (! $isNewConnector) {
            array_walk($connectorConfig['allowed_types'], function ($allowedType) use ($connectorId, $config) {
                if (! in_array($allowedType, $config->getAllAvailableProcessingTypes())) throw new \InvalidArgumentException(sprintf('Allowed data type %s is not known by the system in connector config %s', $allowedType, $connectorId));
            });
        }

        if (isset($connectorConfig['metadata'])) {
            if (!is_array($connectorConfig['metadata'])) throw new \InvalidArgumentException('Metadata must be an array in connector config '. $connectorId);
        }

        if (! $isNewConnector) {
            if (isset($connectorConfig['preferred_type'])) {
                if (! in_array($connectorConfig['preferred_type'], $config->getAllAvailableProcessingTypes())) throw new \InvalidArgumentException(sprintf('Preferred data type %s is not known by the system in connector config %s', $connectorConfig['preferred_type'], $connectorId));
            }
        }
    }

    /**
     * @return array
     */
    private function getAvailableMessageTypes()
    {
        if (is_null($this->availableMessageTypes)) {
            $this->availableMessageTypes = [
                MessageNameUtils::COLLECT_DATA,
                MessageNameUtils::DATA_COLLECTED,
                MessageNameUtils::PROCESS_DATA,
                MessageNameUtils::DATA_PROCESSED,
            ];
        }

        return $this->availableMessageTypes;
    }
}
 
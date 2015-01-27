<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'system_config_dir' => \SystemConfig\Definition::getSystemConfigDir(),
    'system_data_dir' => \SystemConfig\Definition::getDataDir(),
    'system_data_type_dir' => \SystemConfig\Definition::getDataDir() . DIRECTORY_SEPARATOR . "Application" . DIRECTORY_SEPARATOR . "DataType",
    'locations' => [
        'inbox'  => __DIR__ . '/../../data/inbox',
        'outbox' => __DIR__ . '/../../data/outbox',
    ],
    //Default ginger plugins
    //We work with key-value pairs to allow overriding plugins with custom implementations
    //The value (the plugin) must be a service alias
    'ginger' => [
        'plugins' => [
            \ProcessorProxy\GingerPlugin\StartMessageProcessIdLogger::PLUGIN_NAME => \ProcessorProxy\GingerPlugin\StartMessageProcessIdLogger::PLUGIN_NAME,
            \ProcessorProxy\GingerPlugin\MessageFlowLogger::PLUGIN_NAME   => \ProcessorProxy\GingerPlugin\MessageFlowLogger::PLUGIN_NAME,
            \Gingerwork\Monitor\GingerPlugin\ProcessLogListener::PLUGIN_NAME => \Gingerwork\Monitor\GingerPlugin\ProcessLogListener::PLUGIN_NAME,
        ]
    ],
    //Ginger environment defaults
    'service_manager' => [
        'invokables' => array(
            'Doctrine\\ORM\\Mapping\\UnderscoreNamingStrategy' => 'Doctrine\\ORM\\Mapping\\UnderscoreNamingStrategy',
        ),
        'factories' => [
            'ginger_environment' => 'Application\Service\Factory\GingerEnvironmentFactory',
            \Ginger\Processor\Definition::SERVICE_WORKFLOW_PROCESSOR       => 'Ginger\Environment\Factory\WorkflowProcessorFactory',
            \Ginger\Processor\Definition::SERVICE_PROCESS_FACTORY          => 'Ginger\Environment\Factory\ProcessFactoryFactory',
            \Ginger\Processor\Definition::SERVICE_PROCESS_REPOSITORY       => 'Ginger\Environment\Factory\ProcessRepositoryFactory',
        ],
        'abstract_factories' => [
            //ProophServiceBus section
            //The factory creates the channels (ProophServiceBus buses) for the Ginger\Environment\ServicesAwareWorkflowEngine
            //It listens on the requested names "ginger.command_bus.*" and "ginger.event_bus.*" and creates buses with
            //special ginger environment plugins
            'Ginger\Environment\Factory\AbstractServiceBusFactory'
        ],
        'aliases' => [
            //We tell doctrine that it should use the application db connection instead of creating an own connection
            'doctrine.connection.orm_default' => 'application.db',
        ]
    ],
    'doctrine' => array(
        'configuration' => array(
            'orm_default' => array(
                'naming_strategy' => 'Doctrine\\ORM\\Mapping\\UnderscoreNamingStrategy',
                // Generate proxies automatically (turn off for production)
                'generate_proxies'  => false,
                // metadata cache instance to use. The retrieved service name will
                // be `doctrine.cache.$thisSetting`
                'metadata_cache'    => 'filesystem',

                // DQL queries parsing cache instance to use. The retrieved service
                // name will be `doctrine.cache.$thisSetting`
                'query_cache'       => 'filesystem',
            ),
        ),
        'migrations_configuration' => array(
            'orm_default' => array(
                'directory' => 'data/migrations',
                'name' => 'System Database Migrations',
                'namespace' => 'Application\\Migrations',
                'table' => 'migrations',
            ),
        ),
    ),
);

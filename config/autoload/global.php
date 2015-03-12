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
    'system_config_dir' => \Prooph\Link\Application\Definition::getSystemConfigDir(),
    'system_data_dir' => \Prooph\Link\Application\Definition::getDataDir(),
    'system_data_type_dir' => \Prooph\Link\Application\Definition::getDataDir() . DIRECTORY_SEPARATOR . "Application" . DIRECTORY_SEPARATOR . "DataType",
    'locations' => [
        'inbox'  => __DIR__ . '/../../data/inbox',
        'outbox' => __DIR__ . '/../../data/outbox',
    ],
    //Default processing plugins
    //We work with key-value pairs to allow overriding plugins with custom implementations
    //The value (the plugin) must be a service alias
    'processing' => [
        'plugins' => [
            \Prooph\Link\ProcessorProxy\ProcessingPlugin\StartMessageProcessIdLogger::PLUGIN_NAME => \Prooph\Link\ProcessorProxy\ProcessingPlugin\StartMessageProcessIdLogger::PLUGIN_NAME,
            \Prooph\Link\ProcessorProxy\ProcessingPlugin\MessageFlowLogger::PLUGIN_NAME   => \Prooph\Link\ProcessorProxy\ProcessingPlugin\MessageFlowLogger::PLUGIN_NAME,
            \Prooph\Link\Monitor\ProcessingPlugin\ProcessLogListener::PLUGIN_NAME => \Prooph\Link\Monitor\ProcessingPlugin\ProcessLogListener::PLUGIN_NAME,
        ]
    ],
    //Processing environment defaults
    'service_manager' => [
        'invokables' => array(
            'Doctrine\\ORM\\Mapping\\UnderscoreNamingStrategy' => 'Doctrine\\ORM\\Mapping\\UnderscoreNamingStrategy',
        ),
        'factories' => [
            'processing_environment' => \Prooph\Link\Application\Service\Factory\ProcessingEnvironmentFactory::class,
            \Prooph\Processing\Processor\Definition::SERVICE_WORKFLOW_PROCESSOR       => \Prooph\Processing\Environment\Factory\WorkflowProcessorFactory::class,
            \Prooph\Processing\Processor\Definition::SERVICE_PROCESS_FACTORY          => \Prooph\Processing\Environment\Factory\ProcessFactoryFactory::class,
            \Prooph\Processing\Processor\Definition::SERVICE_PROCESS_REPOSITORY       => \Prooph\Processing\Environment\Factory\ProcessRepositoryFactory::class,
        ],
        'abstract_factories' => [
            //ProophServiceBus section
            //The factory creates the channels (ProophServiceBus buses) for the Prooph\Processing\Environment\ServicesAwareWorkflowEngine
            //It listens on the requested names "processing.command_bus.*" and "processing.event_bus.*" and creates buses with
            //special processing environment plugins
            \Prooph\Processing\Environment\Factory\AbstractChannelFactory::class,
        ],
        'aliases' => [
            //We tell doctrine that it should use the application db connection instead of creating an own connection
            'doctrine.connection.orm_default' => 'prooph.link.app.db',
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

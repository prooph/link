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
    'locations' => [
        'inbox'  => __DIR__ . '/../../data/inbox',
        'outbox' => __DIR__ . '/../../data/outbox',
    ],
    //Default ginger plugins
    //We work with key-value pairs to allow overriding plugins with custom implementations
    //The value (the plugin) must be a service alias
    'ginger' => [
        'plugins' => [
            \ProcessorProxy\GingerPlugin\StartMessageLogger::PLUGIN_NAME => 'processor_proxy.start_message_logger',
        ]
    ],
    //Ginger environment defaults
    'service_manager' => [
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
    ],
);

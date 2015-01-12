<?php
return [
    'router' => [
        'routes' => [
            'processor_proxy' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/processor-proxy',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'api' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/api',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'messages' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/messages[/:id]',
                                    'constraints' => array(
                                        'id' => '.+',
                                    ),
                                    'defaults' => [
                                        'controller' => 'ProcessorProxy\Api\Message',
                                    ]
                                ]
                            ],
                            'collect_data_triggers' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/collect-data-triggers[/:id]',
                                    'constraints' => array(
                                        'id' => '.+',
                                    ),
                                    'defaults' => [
                                        'controller' => 'ProcessorProxy\Api\CollectDataTrigger',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'processor_proxy.forward_message_extractor_translator' => 'ProcessorProxy\ProophPlugin\Factory\ForwardMessageExtractorTranslatorFactory',
            'processor_proxy.in_memory_message_forwarder' => 'ProcessorProxy\ProophPlugin\Factory\InMemoryMessageForwarderFactory',
            \ProcessorProxy\GingerPlugin\StartMessageProcessIdLogger::PLUGIN_NAME => 'ProcessorProxy\GingerPlugin\Factory\StartMessageProcessIdLoggerFactory',
            'processor_proxy.message_logger' => 'ProcessorProxy\Service\Factory\DbalMessageLoggerFactory',
        ]
    ],
    'controllers' => array(
        'invokables' => [
            'ProcessorProxy\Api\Message' => 'ProcessorProxy\Api\Message',
        ],
        'factories' => [
            'ProcessorProxy\Api\CollectDataTrigger' => 'ProcessorProxy\Api\Factory\CollectDataTriggerFactory',
        ]
    ),
    'prooph.psb' => [
        'command_router_map' => [
            //By default a service bus message received by the processor proxy API is wrapped with a ForwardMessage command
            //and then forwarded to the ProcessorProxy\ProophPlugin\InMemoryMessageForwarder which forwards
            //the wrapped message to the ginger workflow engine
            //An add on can override the routing so that the ForwardMessage is send to a message dispatcher which puts
            //the wrapped service bus message into a worker queue so that the API service can respond fast and
            //don't have to wait until the message was processed by the workflow processor
            'ProcessorProxy\Command\ForwardHttpMessage' => 'processor_proxy.in_memory_message_forwarder',
        ]
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'ProcessorProxy\Api\Message'            => 'Json',
            'ProcessorProxy\Api\CollectDataTrigger' => 'Json',
        ],
        'accept_whitelist' => [
            'ProcessorProxy\Api\Message'            => ['application/json'],
            'ProcessorProxy\Api\CollectDataTrigger' => ['application/json'],
        ],
        'content_type_whitelist' => [
            'ProcessorProxy\Api\Message'            => ['application/json'],
            'ProcessorProxy\Api\CollectDataTrigger' => ['application/json'],
        ],
    ],
];
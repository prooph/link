<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 06.12.14 - 22:26
 */
return array(
    'dashboard' => [
        'sqlconnector_config_widget' => [
            'controller' => 'SqlConnector\Controller\DashboardWidget',
            'order' => 90 //50 - 99 connectors range
        ]
    ],
    'router' => [
        'routes' => [
            'sql_connector' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/sql-connector',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'configurator' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/sql-manager',
                            'defaults' => [
                                'controller' => 'SqlConnector\Controller\SqlManager',
                                'action' => 'start-app'
                            ]
                        ]
                    ],
                    'api' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/api',
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'connector' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/connectors[/:id]',
                                    'constraints' => array(
                                        'id' => '.+',
                                    ),
                                    'defaults' => [
                                        'controller' => 'SqlConnector\Api\Connector',
                                    ]
                                ]
                            ],
                            'test-connection' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/test-connections[/:id]',
                                    'constraints' => array(
                                        'id' => '.+',
                                    ),
                                    'defaults' => [
                                        'controller' => 'SqlConnector\Api\TestConnection',
                                    ]
                                ]
                            ],
                            'connection' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/connections[/:id]',
                                    'constraints' => array(
                                        'id' => '.+',
                                    ),
                                    'defaults' => [
                                        'controller' => 'SqlConnector\Api\Connection',
                                    ]
                                ]
                            ],
                            'table' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/connections/:dbname/tables[/:name]',
                                    'constraints' => array(
                                        'dbname' => '.+',
                                        'name' => '.+',
                                    ),
                                    'defaults' => [
                                        'controller' => 'SqlConnector\Api\Table',
                                    ]
                                ]
                            ],
                        ],
                    ]
                ],
            ]
        ]
    ],
    'sqlconnector' => [
        //Placeholder for configured connections. The UI creates a sqlconnector.local.php in config/autoload and puts
        //all connections there. The connections are aliased and only the alias is put in the metadata
        //of a connector definition. This ensures that sensitive connection params are not available in the UI except the
        //sqlconnector UI itself.
        'connections' => [],
        //Doctrine type to ProcessingType map
        'doctrine_processing_type_map' => [
            'string' => 'Prooph\Processing\Type\String',
            'text' => 'Prooph\Processing\Type\String',
            'binary' => 'Prooph\Processing\Type\String',
            'blob' => 'Prooph\Processing\Type\String',
            'guid' => 'Prooph\Processing\Type\String',
            'integer' => 'Prooph\Processing\Type\Integer',
            'smallint' => 'Prooph\Processing\Type\Integer',
            'bigint' => 'Prooph\Processing\Type\String',
            'float' => 'Prooph\Processing\Type\Float',
            'decimal' => 'Prooph\Processing\Type\Float',
            'boolean' => 'Prooph\Processing\Type\Boolean',
            'datetime' => 'Prooph\Processing\Type\DateTime',
            'datetimetz' => 'Prooph\Processing\Type\DateTime',
            'date' => 'Prooph\Processing\Type\DateTime',
            'time' => 'Prooph\Processing\Type\DateTime',
        ]
    ],
    'view_manager' => array(
        'template_map' => [
            'sqlconnector/dashboard/widget' => __DIR__ . '/../view/sqlconnector/dashboard/widget.phtml',
            'sqlconnector/partials/pm-metadata-config' => __DIR__ . '/../view/sqlconnector/partials/pm-metadata-config.phtml',
            'sqlconnector/sql-manager/app' => __DIR__ . '/../view/sqlconnector/sql-manager/app.phtml',
            'sqlconnector/sql-manager/partial/sidebar-left' => __DIR__ . '/../view/sqlconnector/sql-manager/partial/sidebar-left.phtml',
        ],
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            //Riot tags are resolved by the Application\Service\RiotTagCollectionResolver
            'riot-tags' => [
                'js/sqlconnector/app.js' => [
                    'sqlconnector/sql-manager/riot-tag/sql-manager',
                    'sqlconnector/sql-manager/riot-tag/connector-list',
                    'sqlconnector/sql-manager/riot-tag/connector-details',
                    'sqlconnector/sql-manager/riot-tag/connection-config',
                ],
                //Inject process manager metadata configurator for sql connectors
                'js/process-config/app.js' => [
                    'sqlconnector/pm/riot-tag/sqlconnector-metadata',
                ],
            ],
            'paths' => array(
                __DIR__ . '/../public',
            ),
        ),
    ),
    'service_manager' => [
        'factories' => [
            'sqlconnector.dbal_connections' => 'SqlConnector\Service\Factory\ConnectionsProvider',
            'sqlconnector.connection_manager' => 'SqlConnector\Service\Factory\ConnectionManagerFactory',
            'sqlconnector.table_connector_generator' => 'SqlConnector\Service\Factory\TableConnectorGeneratorFactory',
        ],
        'abstract_factories' => [
            'SqlConnector\Service\Factory\AbstractDoctrineTableGatewayFactory'
        ]
    ],
    'controllers' => array(
        'invokables' => [
            'SqlConnector\Api\TestConnection' => 'SqlConnector\Api\TestConnection',
            'SqlConnector\Controller\DashboardWidget' => 'SqlConnector\Controller\DashboardWidgetController',
        ],
        'factories' => [
            'SqlConnector\Controller\SqlManager' => 'SqlConnector\Controller\Factory\SqlManagerControllerFactory',
            'SqlConnector\Api\Connector'        => 'SqlConnector\Api\Factory\ConnectorResourceFactory',
            'SqlConnector\Api\Connection'        => 'SqlConnector\Api\Factory\ConnectionResourceFactory',
            'SqlConnector\Api\Table'             => 'SqlConnector\Api\Factory\TableResourceFactory',
        ]
    ),
    'prooph.psb' => [
        'command_router_map' => [

        ]
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'SqlConnector\Api\TestConnection' => 'Json',
            'SqlConnector\Api\Connection' => 'Json',
            'SqlConnector\Api\Connector' => 'Json',
            'SqlConnector\Api\Table' => 'Json',
        ],
        'accept_whitelist' => [
            'SqlConnector\Api\TestConnection' => ['application/json'],
            'SqlConnector\Api\Connection' => ['application/json'],
            'SqlConnector\Api\Connector' => ['application/json'],
            'SqlConnector\Api\Table' => ['application/json'],
        ],
        'content_type_whitelist' => [
            'SqlConnector\Api\TestConnection' => ['application/json'],
            'SqlConnector\Api\Connection' => ['application/json'],
            'SqlConnector\Api\Connector' => ['application/json'],
            'SqlConnector\Api\Table' => ['application/json'],
        ],
    ]
);
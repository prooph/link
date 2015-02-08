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
        'system_config_widget' => [
            'controller' => 'SystemConfig\Controller\DashboardWidget',
            'order' => 101 //100 - 200 config order range
        ]
    ],
    'router' => [
        'routes' => [
            'system_config' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/system-config',
                    'defaults' => array(
                        'controller' => 'SystemConfig\Controller\Overview',
                        'action'     => 'show',
                    ),
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'processing_set_up' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/processing-set-up',
                            'defaults' => [
                                'controller' => 'SystemConfig\Controller\ProcessingSetUp',
                                'action' => 'run'
                            ]
                        ],
                    ],
                    'change_node_name' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/change-node-name',
                            'defaults' => [
                                'controller' => 'SystemConfig\Controller\Configuration',
                                'action' => 'change-node-name'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'view_manager' => array(
        'template_map' => [
            'system-config/dashboard/widget' => __DIR__ . '/../view/system-config/dashboard/widget.phtml',
            'system-config/overview/show' => __DIR__ . '/../view/system-config/overview/show.phtml',
        ],
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            //Riot tags are resolved by the Application\Service\RiotTagCollectionResolver
            'riot-tags' => [
                'js/system-config/app.js' => [
                    'system-config/riot-tag/system-configurator',
                    'system-config/riot-tag/system-node-name',
                ],
            ],
            'paths' => array(
                __DIR__ . '/../public',
            ),
        ),
    ),
    'service_manager' => [
        'invokables' => [
            //System config writer
            'system_config.config_writer' => 'SystemConfig\Service\ConfigWriter\ZendPhpArrayWriter',
            //Command handlers
            'SystemConfig\Model\ProcessingConfig\CreateDefaultConfigFileHandler' => 'SystemConfig\Model\ProcessingConfig\CreateDefaultConfigFileHandler',
            'SystemConfig\Model\ProcessingConfig\InitializeEventStoreHandler'    => 'SystemConfig\Model\ProcessingConfig\InitializeEventStoreHandler',
            'SystemConfig\Model\ProcessingConfig\ChangeNodeNameHandler'          => 'SystemConfig\Model\ProcessingConfig\ChangeNodeNameHandler',
            'SystemConfig\Model\ProcessingConfig\AddNewProcessToConfigHandler'   => 'SystemConfig\Model\ProcessingConfig\AddNewProcessToConfigHandler' ,
            'SystemConfig\Model\ProcessingConfig\ChangeProcessConfigHandler'     => 'SystemConfig\Model\ProcessingConfig\ChangeProcessConfigHandler',
            'SystemConfig\Model\ProcessingConfig\UndoSystemSetUpHandler'         => 'SystemConfig\Model\ProcessingConfig\UndoSystemSetUpHandler',
            'SystemConfig\Model\ProcessingConfig\AddConnectorToConfigHandler'    => 'SystemConfig\Model\ProcessingConfig\AddConnectorToConfigHandler',
            'SystemConfig\Model\ProcessingConfig\ChangeConnectorConfigHandler'    => 'SystemConfig\Model\ProcessingConfig\ChangeConnectorConfigHandler',
        ],
        'factories' => [
            //Projections
            'system_config' => SystemConfig\Service\SystemConfigFactory::class,
        ],
        'aliases' => [
            'processing_config' => 'system_config',
        ]
    ],
    'controllers' => array(
        'invokables' => array(
            'SystemConfig\Controller\ProcessingSetUp'   => \SystemConfig\Controller\ProcessingSetUpController::class,
            'SystemConfig\Controller\Configuration'     => \SystemConfig\Controller\ConfigurationController::class,
            'SystemConfig\Controller\DashboardWidget'   => \SystemConfig\Controller\DashboardWidgetController::class,
            'SystemConfig\Controller\Overview'          => \SystemConfig\Controller\OverviewController::class,
        ),
    ),
    'prooph.psb' => [
        'command_router_map' => [
            'SystemConfig\Command\CreateDefaultProcessingConfigFile' => 'SystemConfig\Model\ProcessingConfig\CreateDefaultConfigFileHandler',
            'SystemConfig\Command\InitializeEventStore'          => 'SystemConfig\Model\ProcessingConfig\InitializeEventStoreHandler',
            'SystemConfig\Command\ChangeNodeName'                => 'SystemConfig\Model\ProcessingConfig\ChangeNodeNameHandler',
            'SystemConfig\Command\AddNewProcessToConfig'         => 'SystemConfig\Model\ProcessingConfig\AddNewProcessToConfigHandler',
            'SystemConfig\Command\ChangeProcessConfig'           => 'SystemConfig\Model\ProcessingConfig\ChangeProcessConfigHandler',
            'SystemConfig\Command\UndoSystemSetUp'               => 'SystemConfig\Model\ProcessingConfig\UndoSystemSetUpHandler',
            'SystemConfig\Command\AddConnectorToConfig'          => 'SystemConfig\Model\ProcessingConfig\AddConnectorToConfigHandler',
            'SystemConfig\Command\ChangeConnectorConfig'         => 'SystemConfig\Model\ProcessingConfig\ChangeConnectorConfigHandler',
        ]
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'SystemConfig\Controller\Configuration' => 'Json',
        ],
        'accept_whitelist' => [
            'SystemConfig\Controller\Configuration' => ['application/json'],
        ],
        'content_type_whitelist' => [
            'SystemConfig\Controller\Configuration' => ['application/json'],
        ],
    ]
);
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 06.12.14 - 22:26
 */
return array(
    'router' => [
        'routes' => [
            'process_config' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/process-config',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'configurator' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/process-manager',
                            'defaults' => [
                                'controller' => 'ProcessConfig\Controller\ProcessManager',
                                'action' => 'start-app'
                            ]
                        ]
                    ],
                    'configurator-test' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/process-manager-test',
                            'defaults' => [
                                'controller' => 'ProcessConfig\Controller\ProcessManager',
                                'action' => 'start-test-app'
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
                            'process' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/processes[/:id]',
                                    'constraints' => array(
                                        'id' => '.+',
                                    ),
                                    'defaults' => [
                                        'controller' => 'ProcessConfig\Api\Process',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ]
        ]
    ],
    'dashboard' => [
        'process_config_widget' => [
            'controller' => 'ProcessConfig\Controller\DashboardWidget',
            'order' => 100 //100 - 200 config order range
        ]

    ],
    'view_manager' => array(
        'template_map' => [
            'process-config/dashboard/widget' => __DIR__ . '/../view/process-config/dashboard/widget.phtml',
            'process-config/process-manager/app' => __DIR__ . '/../view/process-config/process-manager/app.phtml',
            'process-config/process-manager/app-test' => __DIR__ . '/../view/process-config/process-manager/app-test.phtml',
            //Partials for ProcessManager
            'process-config/process-manager/partial/sidebar-left'     => __DIR__ . '/../view/process-config/process-manager/partial/sidebar-left.phtml',
        ],
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            'riot-tags' => [
                'js/process-config/app.js' => [
                    'process-config/process-manager/riot-tag/process-manager',
                    'process-config/process-manager/riot-tag/process-list',
                    'process-config/process-manager/riot-tag/process-create',
                    'process-config/process-manager/riot-tag/process-tasklist',
                    'process-config/process-manager/riot-tag/task-edit',
                    'process-config/process-manager/riot-tag/task-desc',
                    'process-config/process-manager/riot-tag/process-name',
                    'process-config/process-manager/riot-tag/process-play',
                ]
            ],
            'paths' => array(
                __DIR__ . '/../public',
            ),
        ),
    ),
    'controllers' => array(
        'factories' => array(
            'ProcessConfig\Controller\DashboardWidget' => 'ProcessConfig\Controller\Factory\DashboardWidgetControllerFactory',
            'ProcessConfig\Controller\ProcessManager' => 'ProcessConfig\Controller\Factory\ProcessManagerControllerFactory',
            'ProcessConfig\Api\Process' => 'ProcessConfig\Api\Factory\ProcessFactory',
        ),
    ),
    'zf-content-negotiation' => [
        'controllers' => [
            'ProcessConfig\Api\Process' => 'Json',
        ],
        'accept_whitelist' => [
            'ProcessConfig\Api\Process' => ['application/json'],
        ],
        'content_type_whitelist' => [
            'ProcessConfig\Api\Process' => ['application/json'],
        ],
    ],
);
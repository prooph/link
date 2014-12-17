<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
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
                    'api' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/api',
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'processes' => [
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
            'order' => 10 //10 - 20 config order range
        ]

    ],
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            'collections' => array(
                'js/app/process-manager-components.js' => array(
                    'js/app/controllers/manager_controller.js',
                    'js/app/controllers/manager_create_controller.js',
                    'js/app/controllers/task_controller.js',
                    'js/app/models/process.js',
                    'js/app/views/data_type_select.js',
                    'js/app/views/helpers.js',
                ),
            ),
            'paths' => array(
                __DIR__ . '/../public',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => [
            'ProcessConfig\Controller\ProcessManager' => 'ProcessConfig\Controller\ProcessManagerController',
            'ProcessConfig\Api\Process' => 'ProcessConfig\Api\Process',
        ],
        'factories' => array(
            'ProcessConfig\Controller\DashboardWidget' => 'ProcessConfig\Controller\Factory\DashboardWidgetControllerFactory'
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
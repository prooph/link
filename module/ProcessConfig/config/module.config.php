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
            'order' => 100 //100 - 200 config order range
        ]

    ],
    'view_manager' => array(
        'template_map' => [
            'process-config/dashboard/widget' => __DIR__ . '/../view/process-config/dashboard/widget.phtml',
            'process-config/process-manager/app' => __DIR__ . '/../view/process-config/process-manager/app.phtml',
            //Partials for ProcessManager
            'process-config/process-manager/partial/manager-index'  => __DIR__ . '/../view/process-config/process-manager/partial/manager-index.phtml',
            'process-config/process-manager/partial/sidebar-left'   => __DIR__ . '/../view/process-config/process-manager/partial/sidebar-left.phtml',
            'process-config/process-manager/partial/create-process' => __DIR__ . '/../view/process-config/process-manager/partial/create-process.phtml',
            'process-config/process-manager/partial/edit-process'   => __DIR__ . '/../view/process-config/process-manager/partial/edit-process.phtml',
        ],
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            'collections' => array(
                'js/process-config/app.js' => array(
                    'js/process-config/controllers/manager_controller.js',
                    'js/process-config/controllers/manager_create_controller.js',
                    'js/process-config/controllers/process_controller.js',
                    'js/process-config/controllers/task_controller.js',
                    'js/process-config/models/process.js',
                    'js/process-config/models/helpers.js',
                    'js/process-config/views/data_type_select.js',
                    'js/process-config/views/helpers.js',
                ),
            ),
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
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
                            'route' => '/configurator',
                            'defaults' => [
                                'controller' => 'ProcessConfig\Controller\ConfigureProcessApp',
                                'action' => 'start-app'
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
    'controllers' => array(
        'invokables' => [
            'ProcessConfig\Controller\ConfigureProcessApp' => 'ProcessConfig\Controller\ConfigureProcessAppController',
        ],
        'factories' => array(
            'ProcessConfig\Controller\DashboardWidget' => 'ProcessConfig\Controller\Factory\DashboardWidgetControllerFactory'
        ),
    ),
);
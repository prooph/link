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
    'dashboard' => [
        'system_config_widget' => [
            'controller' => 'SystemConfig\Controller\DashboardWidget',
            'order' => 11 //10 - 20 config order range
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
                    'ginger_set_up' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/ginger-set-up',
                            'defaults' => [
                                'controller' => 'SystemConfig\Controller\GingerSetUp',
                                'action' => 'start'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'service_manager' => [
        'factories' => [
            'ginger_config_projection' => 'SystemConfig\Projection\Factory\GingerConfigFactory',
            'SystemConfig\Model\GingerConfig\CreateDefaultConfigFileHandler' => 'SystemConfig\Model\GingerConfig\Factory\CreateDefaultConfigFileHandlerFactory'

        ]
    ],
    'controllers' => array(
        'invokables' => array(
            'SystemConfig\Controller\GingerSetUp'     => 'SystemConfig\Controller\GingerSetUpController'
        ),
        'factories' => array(
            'SystemConfig\Controller\DashboardWidget' => 'SystemConfig\Controller\Factory\DashboardWidgetControllerFactory',
            'SystemConfig\Controller\Overview'        => 'SystemConfig\Controller\Factory\OverviewControllerFactory',
        ),
    ),
    'prooph.psb' => [
        'command_router_map' => [
            'SystemConfig\Command\CreateDefaultGingerConfigFile' => 'SystemConfig\Model\GingerConfig\CreateDefaultConfigFileHandler'
        ]
    ],
);
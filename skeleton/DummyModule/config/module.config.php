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
        'dummy_config_widget' => [
            'controller' => 'Dummy\Controller\DashboardWidget',
            'order' => 50 //0 - 49 monitoring plugins range, 50 - 99 connectors range, >= 100 config module range
        ]
    ],
    'router' => [
        'routes' => [

        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'service_manager' => [
        'factories' => [


        ]
    ],
    'controllers' => [
        'invokables' => [
            'Dummy\Controller\DashboardWidget' => 'Dummy\Controller\DashboardWidgetController'
        ]
    ],
    'prooph.psb' => [
        'command_router_map' => [

        ]
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'Dummy\Controller\Configuration' => 'Json',
        ],
        'accept_whitelist' => [
            'Dummy\Controller\Configuration' => ['application/json'],
        ],
        'content_type_whitelist' => [
            'Dummy\Controller\Configuration' => ['application/json'],
        ],
    ]
);
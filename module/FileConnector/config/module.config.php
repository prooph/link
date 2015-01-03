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
    'fileconnector' => [
        //The FileConnector module uses an own plugin manager to resolve file handlers for file types
        //You can configure the file handler manager like a normal service manager
        //The file type is the alias that resolves to a FileConnector\Service\FileHandler
        'file_types' => [
            'invokables' => [
                'csv' => 'FileConnector\Service\FileHandler\LeagueCsvHandler'
            ]

        ]
    ],
    'dashboard' => [
        'fileconnector_config_widget' => [
            'controller' => 'FileConnector\Controller\DashboardWidget',
            'order' => 91 //50 - 99 connectors range
        ]
    ],
    'router' => [
        'routes' => [

        ]
    ],
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'service_manager' => [
        'factories' => [
            'fileconnector.file_handler_manager' => 'FileConnector\Service\FileHandlerManagerFactory',
        ]
    ],
    'controllers' => array(

    ),
    'prooph.psb' => [
        'command_router_map' => [

        ]
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'FileConnector\Controller\Configuration' => 'Json',
        ],
        'accept_whitelist' => [
            'FileConnector\Controller\Configuration' => ['application/json'],
        ],
        'content_type_whitelist' => [
            'FileConnector\Controller\Configuration' => ['application/json'],
        ],
    ],
);
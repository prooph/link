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
        //The FileConnector module uses an own plugin manager to resolve file type adapters for file types
        //You can configure the file type adapter manager like a normal service manager
        //The file type is the alias that resolves to a FileConnector\Service\FileTypeAdapter
        'file_types' => [
            'invokables' => [
                'csv'  => 'FileConnector\Service\FileTypeAdapter\LeagueCsvTypeAdapter',
                'json' => 'FileConnector\Service\FileTypeAdapter\JsonTypeAdapter',
            ]

        ],
        //Filename templates are rendered with a mustache template engine. Mixins extend mustache with additional functions
        //A MixinManager is used to resolve mixins.
        //A Mixin should implement the __invoke() method to be used as a callable.
        //The alias of the mixin should also be used in the template.
        'filename_mixins' => [
            'invokables' => [
                'now' => 'FileConnector\Service\FileNameRenderer\Mixin\NowMixin',
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
            'file_connector' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/file-connector',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'configurator' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/file-manager',
                            'defaults' => [
                                'controller' => 'FileConnector\Controller\FileManager',
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
                            'connectors' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/connectors[/:id]',
                                    'constraints' => array(
                                        'id' => '.+',
                                    ),
                                    'defaults' => [
                                        'controller' => 'FileConnector\Api\Connector',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ]
        ]
    ],
    'view_manager' => array(
        'template_map' => [
            'file-connector/dashboard/widget' => __DIR__ . '/../view/file-connector/dashboard/widget.phtml',
            'file-connector/file-manager/app' => __DIR__ . '/../view/file-connector/file-manager/app.phtml',
            //Partials for FileConnectorManager
            'file-connector/file-manager/partial/connectors' => __DIR__ . '/../view/file-connector/file-manager/partial/connectors.phtml',
            'file-connector/file-manager/partial/connector-create' => __DIR__ . '/../view/file-connector/file-manager/partial/connector-create.phtml',
            'file-connector/file-manager/partial/sidebar-left' => __DIR__ . '/../view/file-connector/file-manager/partial/sidebar-left.phtml',
        ],
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            'collections' => array(
                'js/file-connector/app.js' => array(
                    'js/file-connector/controllers/connectors-index.js',
                    'js/file-connector/models/connector.js',
                ),
            ),
            'paths' => array(
                __DIR__ . '/../public',
            ),
        ),
    ),
    'service_manager' => [
        'factories' => [
            'fileconnector.file_type_adapter_manager' => 'FileConnector\Service\FileTypeAdapter\FileTypeAdapterManagerFactory',
            'fileconnector.filename_mixin_manager'    => 'FileConnector\Service\FileNameRenderer\MixinManagerFactory',
            'fileconnector.filename_renderer'         => 'FileConnector\Service\FileNameRenderer\FileNameRendererFactory',
        ],
        'abstract_factories' => [
            //Resolves a alias starting with "filegateway:::" to a FileConnector\Service\FileGateway
            'FileConnector\Service\FileGateway\AbstractFileGatewayFactory',
        ]
    ],
    'controllers' => array(
        'invokables' => [
            'FileConnector\Controller\DashboardWidget' => 'FileConnector\Controller\DashboardWidgetController',
            'FileConnector\Controller\FileManager' => 'FileConnector\Controller\FileManagerController',
        ]
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
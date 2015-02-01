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
                                        'controller' => 'FileConnector\Api\FileConnector',
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
            'file-connector/file-manager/partial/connectors'        => __DIR__ . '/../view/file-connector/file-manager/partial/connectors.phtml',
            'file-connector/file-manager/partial/connector-edit'    => __DIR__ . '/../view/file-connector/file-manager/partial/connector-edit.phtml',
            'file-connector/file-manager/partial/sidebar-left'      => __DIR__ . '/../view/file-connector/file-manager/partial/sidebar-left.phtml',
        ],
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'process_manager' => [
        'view_addons' => [
            'file-connector/file-manager/partial/pm-metadata-config'
        ]
    ],
    'asset_manager' => array(
        'resolver_configs' => array(
            'collections' => array(
                'js/file-connector/app.js' => array(
                    'js/file-connector/controllers/connectors-index.js',
                    'js/file-connector/controllers/connectors-create.js',
                    'js/file-connector/controllers/connector-edit.js',
                    'js/file-connector/models/connector.js',
                    'js/file-connector/views/helpers.js',
                ),
            ),
            'riot-tags' => [
                //Inject process manager metadata configurator for file connectors
                'js/process-config/app.js' => [
                    'file-connector/pm/riot-tag/fileconnector-metadata',
                ],
            ],
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
        'factories' => [
            'FileConnector\Controller\FileManager' => 'FileConnector\Controller\Factory\FileManagerControllerFactory',
            'FileConnector\Api\FileConnector'          => 'FileConnector\Api\Factory\FileConnectorFactory',
        ],
        'invokables' => [
            'FileConnector\Controller\DashboardWidget' => 'FileConnector\Controller\DashboardWidgetController',
        ]
    ),
    'prooph.psb' => [
        'command_router_map' => [

        ]
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'FileConnector\Api\FileConnector' => 'Json',
        ],
        'accept_whitelist' => [
            'FileConnector\Api\FileConnector' => ['application/json'],
        ],
        'content_type_whitelist' => [
            'FileConnector\Api\FileConnector' => ['application/json'],
        ],
    ],
);
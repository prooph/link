<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 20.01.15 - 22:20
 */
return [
    'router' => [
        'routes' => [
            'monitor' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/monitor',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'process_overview' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/process-overview',
                            'defaults' => [
                                'controller' => 'Gingerwork\Monitor\Controller\ProcessesOverview',
                                'action' => 'overview'
                            ]
                        ]
                    ],
                    'process_details' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/process-details/[:process_id]',
                            'constraints' => array(
                                'process_id' => '[A-Za-z0-9-]{36,36}',
                            ),
                            'defaults' => [
                                'controller' => 'Gingerwork\Monitor\Controller\ProcessView',
                                'action' => 'details'
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

                        ],
                    ],
                ],
            ],
        ],
    ],
    'dashboard' => [
        'gingerwork_monitor_widget' => [
            'controller' => 'Gingerwork\Monitor\Controller\ProcessesOverview',
            'order' => 1 //Monitoring should be the first widget
        ]

    ],
    'service_manager' => [
        'factories' => [
            'gingerwork.monitor.process_logger' => 'Gingerwork\Monitor\Service\Factory\DbalProcessLoggerFactory',
            'gingerwork.monitor.process_stream_reader' => 'Gingerwork\Monitor\Projection\Factory\ProcessStreamReaderFactory',
            \Gingerwork\Monitor\GingerPlugin\ProcessLogListener::PLUGIN_NAME => 'Gingerwork\Monitor\GingerPlugin\Factory\ProcessLogListenerFactory',
        ]
    ],
    'controllers' => [
        'factories' => [
            'Gingerwork\Monitor\Controller\ProcessesOverview' => 'Gingerwork\Monitor\Controller\Factory\ProcessesOverviewControllerFactory',
            'Gingerwork\Monitor\Controller\ProcessView' => 'Gingerwork\Monitor\Controller\Factory\ProcessViewControllerFactory',
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'gingerwork/monitor/process-view/overview' => __DIR__ . '/../view/gingerwork/monitor/process-view/overview.phtml',
            'gingerwork/monitor/process-view/process-details-app' => __DIR__ . '/../view/gingerwork/monitor/process-view/process-details-app.phtml',
            'gingerwork/monitor/process-view/process-details-incomplete' => __DIR__ . '/../view/gingerwork/monitor/process-view/process-details-incomplete.phtml',
            'gingerwork/monitor/process-view/partial/process-list' => __DIR__ . '/../view/gingerwork/monitor/process-view/partial/process-list.phtml',
            'gingerwork/monitor/process-view/partial/process-details' => __DIR__ . '/../view/gingerwork/monitor/process-view/partial/process-details.phtml',
        ],
    ],
    'asset_manager' => array(
        'resolver_configs' => array(
            'collections' => array(
                'js/process-monitor/app.js' => array(
                    'js/process-monitor/models/process.js',
                    'js/process-monitor/controllers/process_controller.js',
                    'js/process-monitor/controllers/task_event_controller.js',
                    'js/process-monitor/views/helpers.js',
                    'js/process-monitor/views/process_view.js',
                    'js/process-monitor/views/task_event_view.js',
                ),
            ),
            'paths' => array(
                __DIR__ . '/../public',
            ),
        ),
    ),
];
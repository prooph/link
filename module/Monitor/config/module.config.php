<?php
/*
* This file is part of prooph/link.
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
                                'controller' => 'Prooph\Link\Monitor\Controller\ProcessesOverview',
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
                                'controller' => 'Prooph\Link\Monitor\Controller\ProcessView',
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
        'prooph_link_monitor_widget' => [
            'controller' => 'Prooph\Link\Monitor\Controller\ProcessesOverview',
            'order' => 1 //Monitoring should be the first widget
        ]

    ],
    'service_manager' => [
        'factories' => [
            'prooph.link.monitor.process_logger' => 'Prooph\Link\Monitor\Service\Factory\DbalProcessLoggerFactory',
            'prooph.link.monitor.process_stream_reader' => 'Prooph\Link\Monitor\Projection\Factory\ProcessStreamReaderFactory',
            \Prooph\Link\Monitor\ProcessingPlugin\ProcessLogListener::PLUGIN_NAME => 'Prooph\Link\Monitor\ProcessingPlugin\Factory\ProcessLogListenerFactory',
        ]
    ],
    'controllers' => [
        'factories' => [
            'Prooph\Link\Monitor\Controller\ProcessesOverview' => 'Prooph\Link\Monitor\Controller\Factory\ProcessesOverviewControllerFactory',
            'Prooph\Link\Monitor\Controller\ProcessView' => 'Prooph\Link\Monitor\Controller\Factory\ProcessViewControllerFactory',
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'prooph/link/monitor/process-view/overview' => __DIR__ . '/../view/prooph/link/monitor/process-view/overview.phtml',
            'prooph/link/monitor/process-view/process-details-app' => __DIR__ . '/../view/prooph/link/monitor/process-view/process-details-app.phtml',
            'prooph/link/monitor/process-view/process-details-incomplete' => __DIR__ . '/../view/prooph/link/monitor/process-view/process-details-incomplete.phtml',
            'prooph/link/monitor/process-view/partial/process-list' => __DIR__ . '/../view/prooph/link/monitor/process-view/partial/process-list.phtml',
            'prooph/link/monitor/process-view/partial/process-details' => __DIR__ . '/../view/prooph/link/monitor/process-view/partial/process-details.phtml',
        ],
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ],
    'asset_manager' => array(
        'resolver_configs' => array(
            'riot-tags' => [
                'js/process-monitor/app.js' => [
                    'prooph/link/monitor/process-view/riot-tag/process-monitor',
                    'prooph/link/monitor/process-view/riot-tag/task-monitor',
                    'prooph/link/monitor/process-view/riot-tag/task-status',
                    'prooph/link/monitor/process-view/riot-tag/task-event-monitor',
                    'process-config/process-manager/riot-tag/task-desc',
                    'process-config/process-manager/riot-tag/process-play',
                ]
            ],
            'paths' => array(
                __DIR__ . '/../public',
            ),
        ),
    ),
];
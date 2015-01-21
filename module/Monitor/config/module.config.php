<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
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
                                'controller' => 'Gingerwork\Monitor\Controller\ProcessView',
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
                    ]
                ],
            ],
        ],
    ],
    'dashboard' => [
        'gingerwork_monitor_widget' => [
            'controller' => 'Gingerwork\Monitor\Controller\ProcessView',
            'order' => 1 //Monitoring should be the first widget
        ]

    ],
    'service_manager' => [
        'factories' => [
            'gingerwork.monitor.process_logger' => 'Gingerwork\Monitor\Service\Factory\DbalProcessLoggerFactory',
            \Gingerwork\Monitor\GingerPlugin\ProcessLogListener::PLUGIN_NAME => 'Gingerwork\Monitor\GingerPlugin\Factory\ProcessLogListenerFactory',
        ]
    ],
    'controllers' => [
        'factories' => [
            'Gingerwork\Monitor\Controller\ProcessView' => 'Gingerwork\Monitor\Controller\Factory\ProcessViewControllerFactory',
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'gingerwork/monitor/process-view/overview' => __DIR__ . '/../view/gingerwork/monitor/process-view/overview.phtml',
            'gingerwork/monitor/process-view/partial/process-list' => __DIR__ . '/../view/gingerwork/monitor/process-view/partial/process-list.phtml',
        ],
    ],
];
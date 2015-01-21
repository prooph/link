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
    'service_manager' => [
        'factories' => [
            'gingerwork.monitor.process_logger' => 'Gingerwork\Monitor\Service\Factory\DbalProcessLoggerFactory',
            \Gingerwork\Monitor\GingerPlugin\ProcessLogListener::PLUGIN_NAME => 'Gingerwork\Monitor\GingerPlugin\Factory\ProcessLogListenerFactory',
        ]
    ],
    'view_manager' => [
        'template_map' => [
            'process-config/dashboard/widget' => __DIR__ . '/../view/process-config/dashboard/widget.phtml',
        ],
    ],
];
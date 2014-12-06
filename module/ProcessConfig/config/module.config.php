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
        'ProcessConfig\Controller\DashboardWidget'
    ],
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'controllers' => array(
        'factories' => array(
            'ProcessConfig\Controller\DashboardWidget' => 'ProcessConfig\Controller\Factory\DashboardWidgetControllerFactory'
        ),
    ),
);
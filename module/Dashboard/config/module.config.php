<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 06.12.14 - 21:26
 */

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => [
            'dashboard_provider' => 'Dashboard\Service\Factory\DashboardProviderFactory'
        ],
    ),
    'controllers' => array(
        'invokables' => array(
            'Dashboard\Controller\Index' => 'Dashboard\Controller\IndexController'
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'application/index/index' => __DIR__ . '/../view/dashboard/index/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'factories'=> array(
            'dashboard' => 'Dashboard\View\Helper\Factory\DashboardHelperFactory'
        )
    ),
    // Placeholder for AbstractWidgetControllers
    'dashboard' => [
        /*
        'widget_name' => [
            'controller' => 'controller_alias_loaded_via_controller_loader',
            'order' => 1 //-> order by ASC
        ]
        */
    ],
);

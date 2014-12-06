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

namespace Dashboard\Service\Factory;

use Dashboard\Service\DashboardProvider;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DashboardProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return DashboardProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        $controllerLoader = $serviceLocator->get('ControllerLoader');

        $controllers = [];

        foreach ($config['dashboard'] as $widgetControllerAlias) {
            $controllers[] = $controllerLoader->get($widgetControllerAlias);
        }

        return new DashboardProvider($controllers);
    }
}
 
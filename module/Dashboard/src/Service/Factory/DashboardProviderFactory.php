<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 06.12.14 - 21:26
 */

namespace Dashboard\Service\Factory;

use Assert\Assertion;
use Dashboard\Service\DashboardProvider;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DashboardProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @throws \RuntimeException
     * @return DashboardProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        $controllerLoader = $serviceLocator->get('ControllerLoader');

        $controllers = [];

        $sortArr = [];

        foreach ($config['dashboard'] as $widgetName => $widgetConfig) {

            if (! array_key_exists('controller', $widgetConfig)) {
                throw new \RuntimeException('controller key missing in widget config: ' . $widgetName);
            }

            $sortArr[$widgetName] = (isset($widgetConfig['order']))? (int)$widgetConfig['order'] : 0;
        }

        asort($sortArr);

        foreach ($sortArr as $widgetName => $order) {
            $controllers[] = $controllerLoader->get($config['dashboard'][$widgetName]['controller']);
        }

        return new DashboardProvider($controllers);
    }
}
 
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 00:18
 */

namespace ProcessConfig\Controller\Factory;

use ProcessConfig\Controller\DashboardWidgetController;
use ProcessConfig\Projection\GingerConfig;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DashboardWidgetControllerFactory
 *
 * @package ProcessConfig\Controller\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DashboardWidgetControllerFactory implements  FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new DashboardWidgetController(new GingerConfig());
    }
}
 
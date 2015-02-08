<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 00:18
 */

namespace ProcessConfig\Controller\Factory;

use ProcessConfig\Controller\DashboardWidgetController;
use SystemConfig\Projection\ProcessingConfig;
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
        return new DashboardWidgetController($serviceLocator->getServiceLocator()->get('system_config'));
    }
}
 
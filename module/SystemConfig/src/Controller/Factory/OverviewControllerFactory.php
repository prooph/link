<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 20:29
 */

namespace SystemConfig\Controller\Factory;

use SystemConfig\Controller\OverviewController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class OverviewControllerFactory
 *
 * @package SystemConfig\Controller\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class OverviewControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return OverviewController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new OverviewController($serviceLocator->getServiceLocator()->get('ginger_config_projection'));
    }
}
 
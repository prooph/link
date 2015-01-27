<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 06.12.14 - 23:16
 */

namespace Dashboard\View\Helper\Factory;

use Dashboard\View\Helper\DashboardHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DashboardHelperFactory
 *
 * @package Dashboard\View\Helper\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DashboardHelperFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new DashboardHelper($serviceLocator->getServiceLocator()->get('dashboard_provider'));
    }
}
 
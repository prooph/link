<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 21:03
 */

namespace SystemConfig\Controller\Factory;

use SystemConfig\Controller\GingerSetUpController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class GingerSetUpControllerFactory
 *
 * @package SystemConfig\Controller\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class GingerSetUpControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return GingerSetUpController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new GingerSetUpController($serviceLocator->getServiceLocator()->get('prooph.psb.command_bus'));
    }
}
 
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 25.01.15 - 23:10
 */

namespace SqlConnector\Api\Factory;

use SqlConnector\Api\Connection;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ConnectionResourceFactory
 *
 * @package SqlConnector\Api\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ConnectionResourceFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Connection($serviceLocator->getServiceLocator()->get('sqlconnector.connection_manager'));
    }
}
 
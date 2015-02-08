<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 25.01.15 - 00:42
 */

namespace SqlConnector\Controller\Factory;

use SqlConnector\Controller\SqlManagerController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SqlManagerControllerFactory
 *
 * @package SqlConnector\Controller\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class SqlManagerControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new SqlManagerController($serviceLocator->getServiceLocator()->get('sqlconnector.dbal_connections'));
    }
}
 
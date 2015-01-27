<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 25.01.15 - 22:46
 */

namespace SqlConnector\Service\Factory;

use SqlConnector\Service\ConnectionManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ConnectionManagerFactory
 *
 * @package SqlConnector\Service\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ConnectionManagerFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ConnectionManager(
            $serviceLocator->get('application.config_location'),
            $serviceLocator->get('system_config.config_writer'),
            $serviceLocator->get('sqlconnector.dbal_connections')
        );
    }
}
 
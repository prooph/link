<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 25.01.15 - 22:47
 */

namespace SqlConnector\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ConnectionsProvider
 *
 * @package SqlConnector\Service\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ConnectionsProvider implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $appConfig = $serviceLocator->get('config');

        return new \ArrayObject($appConfig['sqlconnector']['connections']);
    }
}
 
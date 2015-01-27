<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 26.01.15 - 00:27
 */

namespace SqlConnector\Api\Factory;

use SqlConnector\Api\Table;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class TableResourceFactory
 *
 * @package SqlConnector\Api\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class TableResourceFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Table($serviceLocator->getServiceLocator()->get('sqlconnector.dbal_connections'));
    }
}
 
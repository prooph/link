<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/26/15 - 7:32 PM
 */
namespace SqlConnector\Api\Factory;

use SqlConnector\Api\Connector;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ConnectorResourceFactory
 *
 * @package SqlConnector\Api\Factory
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class ConnectorResourceFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Connector();
    }
}
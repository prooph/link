<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/27/15 - 9:27 PM
 */
namespace SqlConnector\Service\Factory;

use SqlConnector\Service\TableConnectorGenerator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class TableConnectorGeneratorFactory
 *
 * @package SqlConnector\Service\Factory
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class TableConnectorGeneratorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new TableConnectorGenerator(
            $serviceLocator->get('sqlconnector.dbal_connections'),
            $serviceLocator->get('application.data_type_location'),
            $serviceLocator->get('application.config_location'),
            $serviceLocator->get('prooph.psb.command_bus'),
            $serviceLocator->get("config")['sqlconnector']['doctrine_processing_type_map']
        );
    }
}
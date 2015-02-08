<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.01.15 - 23:22
 */

namespace SqlConnector\Service\Factory;

use Doctrine\DBAL\DriverManager;
use SqlConnector\Service\DoctrineTableGateway;
use SystemConfig\Projection\ProcessingConfig;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AbstractDoctrineTableGatewayFactory
 *
 * @package SqlConnector\Service\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class AbstractDoctrineTableGatewayFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (strpos($requestedName, 'sqlconnector:::') === 0) {
            /** @var $config ProcessingConfig */
            $config = $serviceLocator->get("processing_config");

            return isset($config->getConnectors()[$requestedName]);
        }

        return false;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        /** @var $config ProcessingConfig */
        $config = $serviceLocator->get("processing_config");

        $connector = $config->getConnectors()[$requestedName];

        if (! isset($connector['dbal_connection'])) throw new \InvalidArgumentException(sprintf('Missing dbal_connection for sql connector %s', $requestedName));
        if (! isset($connector['table'])) throw new \InvalidArgumentException(sprintf('Missing table definition for sql connector %s', $requestedName));

        $appConfig = $serviceLocator->get('config');

        if (! isset($appConfig['sqlconnector']['connections'][$connector['dbal_connection']])) throw new \InvalidArgumentException(sprintf('The DBAL connection %s can not be found. Please check config/autoload/sqlconnector.local.php!', $connector['dbal_connection']));

        return new DoctrineTableGateway(
            DriverManager::getConnection($appConfig['sqlconnector']['connections'][$connector['dbal_connection']]),
            $connector['table']
        );
    }
}
 
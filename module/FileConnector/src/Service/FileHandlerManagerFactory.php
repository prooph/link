<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 20:33
 */

namespace FileConnector\Service;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class FileHandlerManagerFactory
 *
 * @package FileConnector\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FileHandlerManagerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @throws \InvalidArgumentException
     * @return FileHandlerManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        if (! array_key_exists('fileconnector', $config)) throw new \InvalidArgumentException('Missing fileconnector root config key');
        if (! is_array($config['fileconnector'])) throw new \InvalidArgumentException("Config for fileconnector must be an array");
        if (! array_key_exists('file_types', $config['fileconnector'])) throw new \InvalidArgumentException('Missing file_types in fileconnector config');

        $fileHandlers = new FileHandlerManager(new Config($config['fileconnector']['file_types']));

        $fileHandlers->setServiceLocator($serviceLocator);

        return $fileHandlers;
    }
}
 
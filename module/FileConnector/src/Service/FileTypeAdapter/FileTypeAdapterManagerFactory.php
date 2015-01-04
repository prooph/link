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

namespace FileConnector\Service\FileTypeAdapter;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class FileTypeAdapterManagerFactory
 *
 * @package FileConnector\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FileTypeAdapterManagerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @throws \InvalidArgumentException
     * @return FileTypeAdapterManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        if (! array_key_exists('fileconnector', $config)) throw new \InvalidArgumentException('Missing fileconnector root config key');
        if (! is_array($config['fileconnector'])) throw new \InvalidArgumentException("Config for fileconnector must be an array");
        if (! array_key_exists('file_types', $config['fileconnector'])) throw new \InvalidArgumentException('Missing file_types in fileconnector config');

        $fileTypeAdapters = new FileTypeAdapterManager(new Config($config['fileconnector']['file_types']));

        $fileTypeAdapters->setServiceLocator($serviceLocator);

        return $fileTypeAdapters;
    }
}
 
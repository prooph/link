<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.01.15 - 00:05
 */

namespace FileConnector\Service\FileGateway;

use FileConnector\Service\FileGateway;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AbstractFileGatewayFactory
 *
 * If a service alias starts with "filegateway:::" this factory will return the FileGateway WorkflowMessageHandler
 *
 * @package FileConnector\Service\FileGateway
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class AbstractFileGatewayFactory implements AbstractFactoryInterface
{
    private $cachedFileGateway;

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
        return strpos($requestedName, 'filegateway:::') === 0;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (is_null($this->cachedFileGateway)) {
            $this->cachedFileGateway = new FileGateway(
                $serviceLocator->get('fileconnector.file_type_adapter_manager'),
                $serviceLocator->get('fileconnector.filename_renderer')
            );
        }

        return $this->cachedFileGateway;
    }
}
 
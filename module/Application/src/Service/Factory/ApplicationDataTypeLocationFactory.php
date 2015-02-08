<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/27/15 - 8:29 PM
 */
namespace Application\Service\Factory;

use Application\SharedKernel\ApplicationDataTypeLocation;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ApplicationDataTypeLocationFactory
 *
 * @package Application\Service\Factory
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class ApplicationDataTypeLocationFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return ApplicationDataTypeLocation::fromPath($serviceLocator->get('config')['system_data_type_dir']);
    }
}
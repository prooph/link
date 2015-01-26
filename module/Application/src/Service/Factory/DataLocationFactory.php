<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/26/15 - 8:07 PM
 */
namespace Application\Service\Factory;

use Application\SharedKernel\DataLocation;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DataLocationFactory
 *
 * @package Application\Service\Factory
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class DataLocationFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return DataLocation::fromPath($serviceLocator->get('config')['system_data_dir']);
    }
}
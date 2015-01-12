<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.01.15 - 22:34
 */

namespace Application\Service\Factory;

use Ginger\Environment\Environment;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class GingerEnvironmentFactory
 *
 * @package Application\SharedKernel\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class GingerEnvironmentFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return Environment::setUp($serviceLocator);
    }
}
 
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.01.15 - 00:43
 */

namespace ProcessorProxy\GingerPlugin\Factory;

use ProcessorProxy\GingerPlugin\StartMessageLogger;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class StartMessageLoggerFactory
 *
 * @package ProcessorProxy\GingerPlugin\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class StartMessageLoggerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new StartMessageLogger($serviceLocator->get('processor_proxy.message_process_map'));
    }
}
 
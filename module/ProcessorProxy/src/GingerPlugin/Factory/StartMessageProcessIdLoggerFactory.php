<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.01.15 - 00:43
 */

namespace ProcessorProxy\GingerPlugin\Factory;

use ProcessorProxy\GingerPlugin\StartMessageProcessIdLogger;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class StartMessageProcessIdLoggerFactory
 *
 * @package ProcessorProxy\GingerPlugin\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class StartMessageProcessIdLoggerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new StartMessageProcessIdLogger($serviceLocator->get('processor_proxy.message_logger'));
    }
}
 
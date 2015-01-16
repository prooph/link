<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.01.15 - 23:45
 */

namespace ProcessorProxy\GingerPlugin\Factory;

use ProcessorProxy\GingerPlugin\MessageFlowLogger;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class MessageFlowLoggerFactory
 *
 * @package ProcessorProxy\GingerPlugin\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class MessageFlowLoggerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new MessageFlowLogger($serviceLocator->get('processor_proxy.message_logger'));
    }
}
 
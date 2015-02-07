<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.01.15 - 21:54
 */

namespace ProcessorProxy\ProophPlugin\Factory;

use ProcessorProxy\ProophPlugin\ServiceBusMessageExtractor;
use Prooph\ServiceBus\InvokeStrategy\ForwardToMessageDispatcherStrategy;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ForwardMessageExtractorTranslatorFactory
 *
 * @package ProcessorProxy\ProophPlugin\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ForwardMessageExtractorTranslatorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ForwardToMessageDispatcherStrategy(new ServiceBusMessageExtractor());
    }
}
 
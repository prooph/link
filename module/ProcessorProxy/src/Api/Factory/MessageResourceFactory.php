<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.01.15 - 23:37
 */

namespace ProcessorProxy\Api\Factory;

use ProcessorProxy\Api\Message;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class MessageResourceFactory
 *
 * @package ProcessorProxy\Api\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class MessageResourceFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Message($serviceLocator->getServiceLocator()->get('processor_proxy.message_logger'));
    }
}
 
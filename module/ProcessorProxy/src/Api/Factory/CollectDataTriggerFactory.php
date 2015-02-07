<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.01.15 - 22:24
 */

namespace ProcessorProxy\Api\Factory;

use ProcessorProxy\Api\CollectDataTrigger;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class CollectDataTriggerFactory
 *
 * @package ProcessorProxy\Api\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class CollectDataTriggerFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CollectDataTrigger(
            $serviceLocator->getServiceLocator()->get('processor_proxy.message_logger'),
            $serviceLocator->getServiceLocator()->get('system_config')
        );
    }
}
 
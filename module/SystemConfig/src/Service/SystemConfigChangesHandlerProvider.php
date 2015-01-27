<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.01.15 - 21:54
 */

namespace SystemConfig\Service;

use SystemConfig\Service\ConfigWriter\ZendPhpArrayWriter;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SystemConfigChangesHandlerProvider
 *
 * @package SystemConfig\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class SystemConfigChangesHandlerProvider implements InitializerInterface
{
    /**
     * Initialize
     *
     * @param $instance
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof HandlesSystemConfigChanges) {
            $instance->setEventBus($serviceLocator->get('prooph.psb.event_bus'));
            $instance->setConfigWriter($serviceLocator->get('system_config.config_writer'));
        }
    }
}
 
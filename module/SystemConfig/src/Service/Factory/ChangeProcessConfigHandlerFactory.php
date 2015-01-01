<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 30.12.14 - 18:38
 */

namespace SystemConfig\Service\Factory;

use SystemConfig\Model\GingerConfig\ChangeProcessConfigHandler;
use SystemConfig\Service\ConfigWriter\ZendPhpArrayWriter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ChangeProcessConfigHandlerFactory
 *
 * @package SystemConfig\Service\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ChangeProcessConfigHandlerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ChangeProcessConfigHandler(new ZendPhpArrayWriter(), $serviceLocator->get('prooph.psb.event_bus'));
    }
}
 
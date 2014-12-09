<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.12.14 - 23:08
 */

namespace SystemConfig\Service\Factory;

use SystemConfig\Model\GingerConfig\ChangeNodeNameHandler;
use SystemConfig\Service\ConfigWriter\ZendPhpArrayWriter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ChangeNodeNameHandlerFactory
 *
 * @package SystemConfig\Service\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ChangeNodeNameHandlerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ChangeNodeNameHandler
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ChangeNodeNameHandler(new ZendPhpArrayWriter(), $serviceLocator->get('prooph.psb.event_bus'));
    }
}
 
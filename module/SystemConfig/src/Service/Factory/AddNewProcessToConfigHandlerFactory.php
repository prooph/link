<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/14/14 - 10:15 PM
 */
namespace SystemConfig\Service\Factory;

use SystemConfig\Model\GingerConfig\AddNewProcessToConfigHandler;
use SystemConfig\Service\ConfigWriter\ZendPhpArrayWriter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AddNewProcessToConfigHandlerFactory
 *
 * @package SystemConfig\Service\Factory
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class AddNewProcessToConfigHandlerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new AddNewProcessToConfigHandler(new ZendPhpArrayWriter(), $serviceLocator->get('prooph.psb.event_bus'));
    }
}
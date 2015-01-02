<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 02.01.15 - 17:24
 */

namespace SystemConfig\Service\Factory;

use SystemConfig\Model\GingerConfig\UndoSystemSetUpHandler;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class UndoSystemSetUpHandlerFactory
 *
 * @package SystemConfig\Service\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class UndoSystemSetUpHandlerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new UndoSystemSetUpHandler($serviceLocator->get('prooph.psb.event_bus'));
    }
}
 
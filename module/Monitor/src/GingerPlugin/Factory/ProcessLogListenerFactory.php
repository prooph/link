<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 21.01.15 - 17:41
 */

namespace Gingerwork\Monitor\GingerPlugin\Factory;

use Gingerwork\Monitor\GingerPlugin\ProcessLogListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ProcessLogListenerFactory
 *
 * @package Gingerwork\Monitor\GingerPlugin\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ProcessLogListenerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ProcessLogListener($serviceLocator->get('gingerwork.monitor.process_logger'));
    }
}
 
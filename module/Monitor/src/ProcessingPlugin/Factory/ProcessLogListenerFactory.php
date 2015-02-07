<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 21.01.15 - 17:41
 */

namespace Prooph\Link\Monitor\ProcessingPlugin\Factory;

use Prooph\Link\Monitor\ProcessingPlugin\ProcessLogListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ProcessLogListenerFactory
 *
 * @package Prooph\Link\Monitor\ProcessingPlugin\Factory
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
        return new ProcessLogListener($serviceLocator->get('prooph.link.monitor.process_logger'));
    }
}
 
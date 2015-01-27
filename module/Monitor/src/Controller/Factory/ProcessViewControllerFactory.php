<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 21.01.15 - 22:44
 */

namespace Gingerwork\Monitor\Controller\Factory;

use Application\SharedKernel\ScriptLocation;
use Gingerwork\Monitor\Controller\ProcessViewController;
use SystemConfig\Definition;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ProcessViewControllerFactory
 *
 * @package Gingerwork\Monitor\Controller\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ProcessViewControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ProcessViewController(
            $serviceLocator->getServiceLocator()->get('gingerwork.monitor.process_logger'),
            $serviceLocator->getServiceLocator()->get('gingerwork.monitor.process_stream_reader'),
            ScriptLocation::fromPath(Definition::getScriptsDir()),
            $serviceLocator->getServiceLocator()->get('application.location_translator')
        );
    }
}
 
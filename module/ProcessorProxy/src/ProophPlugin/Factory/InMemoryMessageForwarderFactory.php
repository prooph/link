<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.01.15 - 22:30
 */

namespace ProcessorProxy\ProophPlugin\Factory;

use Ginger\Environment\Environment;
use ProcessorProxy\ProophPlugin\InMemoryMessageForwarder;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class InMemoryMessageForwarderFactory
 *
 * @package ProcessorProxy\ProophPlugin\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class InMemoryMessageForwarderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $env Environment */
        $env = $serviceLocator->get('ginger_environment');

        return new InMemoryMessageForwarder($env->getWorkflowEngine());
    }
}
 
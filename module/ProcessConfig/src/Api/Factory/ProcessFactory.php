<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 01.01.15 - 18:29
 */

namespace ProcessConfig\Api\Factory;

use Application\SharedKernel\ScriptLocation;
use ProcessConfig\Api\Process;
use SystemConfig\Definition;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ProcessFactory
 *
 * @package ProcessConfig\Api\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ProcessFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $process = new Process();

        $process->setScriptLocation(ScriptLocation::fromPath(Definition::getScriptsDir()));

        return $process;
    }
}
 
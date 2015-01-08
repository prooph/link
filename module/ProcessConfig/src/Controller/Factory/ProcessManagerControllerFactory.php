<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 01.01.15 - 17:59
 */

namespace ProcessConfig\Controller\Factory;

use Application\SharedKernel\ScriptLocation;
use ProcessConfig\Controller\ProcessManagerController;
use SystemConfig\Definition;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ProcessManagerControllerFactory
 *
 * @package ProcessConfig\Controller\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ProcessManagerControllerFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ProcessManagerController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $con = new ProcessManagerController();
        $con->setScriptLocation(ScriptLocation::fromPath(Definition::getScriptsDir()));

        $config = $serviceLocator->getServiceLocator()->get('config');

        $con->setViewAddons($config['process_manager']['view_addons']);

        $con->setLocationTranslator($serviceLocator->getServiceLocator()->get('application.location_translator'));

        return $con;
    }
}
 
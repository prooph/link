<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 06.12.14 - 21:26
 */

namespace Gingerwork\Monitor;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

/**
 * Class Module
 *
 * This module is responsible for monitoring workflow processes performed by the ginger workflow processor.
 * It registers an own plugin on the ginger environment to log processing states.
 * The collected information is presented in the UI.
 * The monitor module is also able to read the event stream of a process and translate the recorded events
 * into readable information for the user.
 *
 * @package Gingerwork\Monitor
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        //Autoloading is handled by composer. See root composer.json for namespace definition
    }
}

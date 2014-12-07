<?php
/*
 * This file is part of the prooph/ProophServiceBusModule.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.07.14 - 22:42
 */
/**
 * ProophServiceBus Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish. *
 */
$settings = array(
    /**
     * Define a list of utils that should be used by the command bus.
     * Each util should be available as a service.
     * Use the ServiceManager alias in the list.
     */
    'command_bus' => array(
        //Default list
        'prooph.psb.command_router',
        'prooph.psb.service_locator_proxy',
        'prooph.psb.callback_invoke_strategy',
        'prooph.psb.handle_command_invoke_strategy',
    ),
    /**
     * Define a list of utils that should be used by the event bus.
     * Each util should be available as a service.
     * Use the ServiceManager alias in the list.
     */
    'event_bus' => array(
        //Default list
        'prooph.psb.event_router',
        'prooph.psb.service_locator_proxy',
        'prooph.psb.callback_invoke_strategy',
        'prooph.psb.on_event_invoke_strategy',
    ),
    /**
     * Configure command routing
     * @see https://github.com/prooph/service-bus/blob/master/docs/plugins.md#proophservicebusroutercommandrouter
     */
    'command_router_map' => array(

    ),
    /**
     * Configure event routing
     * @see https://github.com/prooph/service-bus/blob/master/docs/plugins.md#proophservicebusroutereventrouter
     */
    'event_router_map' => array(

    )
);

/* DO NOT EDIT BELOW THIS LINE */
return array(
    'prooph.psb' => $settings
);

<?php
/*
 * This file is part of the prooph/ProophServiceBusModule.
* (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.07.14 - 22:42
 */
/**
 * ProophServiceBus Configuration
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
        //We force the rule -one handler per command- with a custom invoke strategy (Application\ProophPlugin\SingleHandleMethodInvokeStrategy)
        'application.psb.single_handle_method_invoke_strategy',
        //This plugin extracts a service bus message out of a ProcessorProxy\Command\ForwardMessage command
        //when the command is send to a message dispatcher
        'processor_proxy.forward_message_extractor_translator',
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

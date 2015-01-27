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

return array(
    // Placeholder for http routes
    'router' => array(
        'routes' => array(
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
    'service_manager' => array(
        'invokables' => [
            'application.psb.single_handle_method_invoke_strategy' => 'Application\ProophPlugin\SingleHandleMethodInvokeStrategy',
        ],
        'factories' => [
            'application.config_location'     => 'Application\Service\Factory\ConfigLocationFactory',
            'application.data_location'       => 'Application\Service\Factory\DataLocationFactory',
            'application.data_type_location'  => 'Application\Service\Factory\ApplicationDataTypeLocationFactory',
            'application.location_translator' => 'Application\SharedKernel\Factory\LocationTranslatorFactory',
            'application.db'                  => 'Application\Service\Factory\ApplicationDbFactory',
            'application.riot_tag.collection.resolver' => 'Application\Service\Factory\RiotTagCollectionResolverFactory',
        ],
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'view_helpers' => array(
        'invokables'=> array(
            'emberPushToStore' => 'Application\View\Helper\EmberPushToStore',
            'riotTag'          => 'Application\View\Helper\RiotTag'
        )
    ),
    'asset_manager' => [
        'resolvers' => [
            'application.riot_tag.collection.resolver' => 2000
        ]
    ],
    'zf-content-negotiation' => [
        //Application wide selectors for the content negotiation module
        'selectors'   => array(
            'Json' => array(
                'ZF\ContentNegotiation\JsonModel' => array(
                    'application/json',
                    'application/*+json',
                ),
            ),
        ),
    ],
    'zf-api-problem' => [
        'accept_filters' => [
            'application/json',
            'application/*+json',
        ]
    ],
    ''
);

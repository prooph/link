<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 25.01.15 - 14:23
 */

namespace Application\Service\Factory;

use Application\Service\RiotTagCollectionResolver;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class RiotTagCollectionResolverFactory
 *
 * @package Application\Service\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class RiotTagCollectionResolverFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config      = $serviceLocator->get('Config');
        $collections = array();

        if (isset($config['asset_manager']['resolver_configs']['riot-tags'])) {
            $collections = $config['asset_manager']['resolver_configs']['riot-tags'];
        }

        return new RiotTagCollectionResolver($collections, $serviceLocator->get('ViewHelperManager')->get('riotTag'));
    }
}
 
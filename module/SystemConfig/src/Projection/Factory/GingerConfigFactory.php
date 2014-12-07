<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 20:38
 */

namespace SystemConfig\Projection\Factory;

use SystemConfig\Projection\GingerConfig;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class GingerConfigFactory
 *
 * @package SystemConfig\Projection\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class GingerConfigFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        $gingerConfig = (isset($config['ginger']))? $config['ginger'] : null;

        return new GingerConfig($gingerConfig);
    }
}
 
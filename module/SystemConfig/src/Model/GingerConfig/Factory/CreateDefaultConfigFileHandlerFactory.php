<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 21:36
 */

namespace SystemConfig\Model\GingerConfig\Factory;

use SystemConfig\Model\ConfigWriter\ZendPhpArrayWriter;
use SystemConfig\Model\GingerConfig\CreateDefaultConfigFileHandler;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class CreateDefaultConfigFileHandlerFactory
 *
 * @package SystemConfig\Model\GingerConfig\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class CreateDefaultConfigFileHandlerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return CreateDefaultConfigFileHandler
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CreateDefaultConfigFileHandler(new ZendPhpArrayWriter());
    }
}
 
<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/23/15 - 2:19 PM
 */
namespace Prooph\Link\Monitor\Projection\Factory;

use Prooph\Link\Monitor\Projection\ProcessStreamReader;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ProcessStreamReaderFactory
 *
 * @package Prooph\Link\Monitor\Projection\Factory
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class ProcessStreamReaderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ProcessStreamReader($serviceLocator->get('prooph.event_store'));
    }
}
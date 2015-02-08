<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 04.01.15 - 21:04
 */

namespace FileConnector\Service\FileNameRenderer;

use FileConnector\Service\FileNameRenderer;
use Phly\Mustache\Mustache;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class FileNameRendererFactory
 *
 * @package FileConnector\Service\FileNameRenderer
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FileNameRendererFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $mixinManager MixinManager */
        $mixinManager = $serviceLocator->get('fileconnector.filename_mixin_manager');

        $mixinNames = $mixinManager->getAvailableMixins();
        $mixins = [];

        foreach ($mixinNames as $mixinName) {
            $mixins[$mixinName] = function () use ($mixinName, $mixinManager) {
                /** @var $mixin callable */
                $mixin = $mixinManager->get($mixinName);

                return $mixin();
            };
        }

        return new FileNameRenderer(new Mustache(), $mixins);
    }
}
 
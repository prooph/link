<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/8/14 - 10:07 PM
 */
namespace Application\Service;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ControllerTranslatorProvider
 *
 * @package Application\Service
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class ControllerTranslatorProvider implements InitializerInterface
{
    /**
     * Initialize
     *
     * @param $instance
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof TranslatorAwareController) {
            $instance->setTranslator($serviceLocator->getServiceLocator()->get('translator'));
        }
    }
}
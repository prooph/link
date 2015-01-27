<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 04.01.15 - 20:24
 */

namespace FileConnector\Service\FileNameRenderer;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;

/**
 * Class MixinManager
 *
 * @package FileConnector\Service\FileNameRenderer
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class MixinManager extends AbstractPluginManager
{
    /**
     * Validate the plugin
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed $plugin
     * @throws \RuntimeException
     * @return void
     */
    public function validatePlugin($plugin)
    {
        if (! is_callable($plugin)) throw new \RuntimeException("Non callable mixin");
    }

    /**
     * @return array
     */
    public function getAvailableMixins()
    {
        return array_merge(array_keys($this->invokableClasses), array_keys($this->factories), array_keys($this->aliases), array_keys($this->instances));
    }
}
 
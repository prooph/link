<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 01.01.15 - 17:47
 */

namespace Application\SharedKernel;

/**
 * Class ScriptLocation
 *
 * This class represents the directory where payload manipulation scripts are located.
 *
 * @package Application\SharedKernel
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ScriptLocation extends AbstractLocation
{
    /**
     * @return array
     */
    public function getScriptNames()
    {
        $files = scandir($this->path);

        return array_values(array_filter($files, function($file) { return (bool)preg_match('/\.php$/', $file); }));
    }

    protected function additionalAssertPath($path)
    {
        if (!is_readable($path)) throw new \InvalidArgumentException(sprintf('Script location %s must be readable', $path));
    }
}
 
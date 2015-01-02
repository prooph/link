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
final class ScriptLocation 
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path
     * @return ScriptLocation
     */
    public static function fromPath($path)
    {
        return new self($path);
    }

    /**
     * @param string $path
     * @throws \InvalidArgumentException
     */
    private function __construct($path)
    {
        if (! is_string($path)) throw new \InvalidArgumentException('Path must be a string');
        if (! is_dir($path)) throw new \InvalidArgumentException(sprintf('Script location %s must be a valid directory path'));
        if (!is_readable($path)) throw new \InvalidArgumentException(sprintf('Script location %s must be readable', $path));

        $this->path = rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getScriptNames()
    {
        $files = scandir($this->path);

        return array_values(array_filter($files, function($file) { return (bool)preg_match('/\.php$/', $file); }));
    }
}
 
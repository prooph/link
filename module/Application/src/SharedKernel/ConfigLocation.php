<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.12.14 - 21:14
 */

namespace Application\SharedKernel;

/**
 * Class ConfigLocation
 *
 * @package SystemConfig\Model\GingerConfig
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ConfigLocation 
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path
     * @return ConfigLocation
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
        if (! is_dir($path)) throw new \InvalidArgumentException(sprintf('Config location %s must be a valid directory path'));
        if (!is_writable($path)) throw new \InvalidArgumentException(sprintf('Config location %s must be writable', $path));

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
     * @param $fileName
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getConfigArray($fileName)
    {
        $configFile = $this->path . DIRECTORY_SEPARATOR . $fileName;

        if (! file_exists($configFile)) throw new \InvalidArgumentException(sprintf('Config file %s does not exist', $configFile));
        if (! preg_match('/\.php$/', $fileName)) throw new \InvalidArgumentException(sprintf('Config file %s must be a php file', $configFile));

        $config = include $configFile;

        if (!is_array($config)) throw new \InvalidArgumentException(sprintf('Config read from %s is not an array', $configFile));

        return $config;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
 
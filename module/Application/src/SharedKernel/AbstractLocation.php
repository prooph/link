<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/26/15 - 7:50 PM
 */
namespace Application\SharedKernel;

/**
 * Class AbstractLocation
 *
 * Base class for locations
 *
 * @package Application\SharedKernel
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
abstract class AbstractLocation
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @param string $path
     * @return static
     */
    public static function fromPath($path)
    {
        return new static($path);
    }

    /**
     * @param string $path
     * @throws \InvalidArgumentException
     */
    private function __construct($path)
    {
        $this->defaultAssertPath($path);
        $this->additionalAssertPath($path);

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
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    abstract protected function additionalAssertPath($path);

    protected function defaultAssertPath($path)
    {
        if (! is_string($path)) throw new \InvalidArgumentException('Path must be a string');
        if (! is_dir($path)) throw new \InvalidArgumentException(sprintf('Path %s must be a valid directory', $path));
    }
} 
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 00:05
 */

namespace SystemConfig\Projection;
use Codeliner\ArrayReader\ArrayReader;

/**
 * Class GingerConfig
 *
 * @package ProcessConfig\Projection
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class GingerConfig
{
    /**
     * @var ArrayReader
     */
    private $config;

    /**
     * @var bool
     */
    private $configured = false;

    /**
     * @param array $gingerConfig
     */
    public function __construct(array $gingerConfig = null)
    {
        if (is_null($gingerConfig)) {
            $this->config = new ArrayReader([]);
        } else {
            $this->configured = true;
            $this->config = new ArrayReader($gingerConfig);
        }
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        return $this->configured;
    }

    /**
     * @return string
     */
    public function getNodeName()
    {
        return $this->config->stringValue('node_name');
    }
}
 
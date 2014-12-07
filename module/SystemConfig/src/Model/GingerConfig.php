<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 21:58
 */

namespace SystemConfig\Model;

use Ginger\Environment\Environment;

/**
 * Class GingerConfig
 *
 * @package SystemConfig\Model
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class GingerConfig 
{
    /**
     * @var array
     */
    private $config = array();

    /**
     * Local config file name
     *
     * @var string
     */
    private $configFileName = 'ginger.config.local.php';


    /**
     * Uses Ginger\Environment to initialize with its defaults
     */
    public static function initializeWithDefaults()
    {
        $env = Environment::setUp();

        return new self(['ginger' => $env->getConfig()->toArray()]);
    }

    /**
     * @param array $config
     */
    private function __construct(array $config)
    {
        $this->setConfig($config);
    }

    /**
     * Returns array representation of the Ginger configuration
     *
     * @return array
     */
    public function toArray()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function configFileName()
    {
        return $this->configFileName;
    }

    /**
     * Assert and set config
     *
     * @param array $config
     */
    private function setConfig(array $config)
    {
        $this->config = $config;
    }
}
 
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
     * @var array
     */
    private $possibleTypes;

    /**
     * @param array $gingerConfig
     * @param bool  $isConfigured
     */
    public function __construct(array $gingerConfig, $isConfigured = false)
    {
        $this->configured = $isConfigured;
        $this->config = new ArrayReader($gingerConfig);
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
        return $this->config->stringValue('ginger.node_name');
    }

    /**
     * @return array
     */
    public function getProcessDefinitions()
    {
        return $this->config->arrayValue('ginger.processes');
    }

    /**
     * @return array
     */
    public function getAllPossibleGingerTypes()
    {
        if (! is_null($this->possibleTypes)) return $this->possibleTypes;

        $possibleTypes = [];

        foreach ($this->config->arrayValue('ginger.connectors') as $connectorConfig) {
            if (! is_array($connectorConfig)) continue;

            foreach ($connectorConfig as $typesDefinition) {
                if (! is_array($typesDefinition)) continue;

                $typesDefinition = new ArrayReader($typesDefinition);

                foreach($typesDefinition->arrayValue('allowed_types') as $allowedType) {
                    $possibleTypes[] = $allowedType;
                }
            }
        }

        $this->possibleTypes = array_unique($possibleTypes);

        return $this->possibleTypes;
    }
}
 
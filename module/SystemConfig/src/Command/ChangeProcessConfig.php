<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 30.12.14 - 16:43
 */

namespace SystemConfig\Command;
use Application\Command\SystemCommand;
use Application\SharedKernel\ConfigLocation;

/**
 * Class ChangeProcessConfig
 *
 * @package SystemConfig\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ChangeProcessConfig extends SystemCommand
{
    /**
     * @param $messageName
     * @param array $processConfig
     * @param ConfigLocation $configLocation
     * @return ChangeProcessConfig
     */
    public static function ofProcessTriggeredByMessage($messageName, array $processConfig, ConfigLocation $configLocation)
    {
        return new self(
            __CLASS__,
            [
                'start_message' => $messageName,
                'process_config' => $processConfig,
                'config_location' => $configLocation->toString()
            ]
        );
    }

    /**
     * @return array
     */
    public function processConfig()
    {
        return $this->payload['process_config'];
    }

    /**
     * @return string
     */
    public function startMessage()
    {
        return $this->payload['start_message'];
    }

    /**
     * @return ConfigLocation
     */
    public function configLocation()
    {
        return ConfigLocation::fromPath($this->payload['config_location']);
    }

    protected function assertPayload($aPayload = null)
    {
        if (! is_array($aPayload)) throw new \InvalidArgumentException("Payload must be an array");
        if (! array_key_exists("start_message",$aPayload)) throw new \InvalidArgumentException("Name of start message missing");
        if (! array_key_exists("process_config",$aPayload)) throw new \InvalidArgumentException("Process config missing");
        if (! is_array($aPayload['process_config'])) throw new \InvalidArgumentException("Process config must be an array");
        if (! array_key_exists("config_location",$aPayload)) throw new \InvalidArgumentException("Config location missing");
    }
}
 
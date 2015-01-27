<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 21:31
 */

namespace SystemConfig\Command;

use Application\Command\SystemCommand;
use Application\SharedKernel\ConfigLocation;

/**
 * Command CreateDefaultGingerConfigFile
 *
 * @package SystemConfig\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class CreateDefaultGingerConfigFile extends SystemCommand
{
    /**
     * @var ConfigLocation
     */
    private $configLocation;

    /**
     * @param ConfigLocation $configLocation
     * @return CreateDefaultGingerConfigFile
     * @throws \InvalidArgumentException
     */
    public static function in(ConfigLocation $configLocation)
    {
        $instance = new self(__CLASS__, ['config_location' => $configLocation->toString()]);

        $instance->configLocation = $configLocation;

        return $instance;
    }

    /**
     * @return ConfigLocation
     */
    public function configLocation()
    {
        if (is_null($this->configLocation)) {
            $this->configLocation = ConfigLocation::fromPath($this->payload['config_location']);
        }
        return $this->configLocation;
    }

    /**
     * @param null|array $aPayload
     * @throws \InvalidArgumentException
     */
    protected function assertPayload($aPayload = null)
    {
        if (! is_array($aPayload) || ! array_key_exists('config_location', $aPayload)) {
            throw new \InvalidArgumentException('Payload does not contain a config_location');
        }

        if (! is_string($aPayload['config_location'])) {
            throw new \InvalidArgumentException("Config location must be string, but type " . gettype($aPayload['config_location']) . " given");
        }
    }
}
 
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 21:31
 */

namespace SystemConfig\Command;

/**
 * Command CreateDefaultGingerConfigFile
 *
 * @package SystemConfig\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class CreateDefaultGingerConfigFile extends AbstractCommand
{
    /**
     * @param string $configLocation
     * @return CreateDefaultGingerConfigFile
     * @throws \InvalidArgumentException
     */
    public static function in($configLocation)
    {
        return new self(__CLASS__, ['config_location' => $configLocation]);
    }

    /**
     * @return string
     */
    public function configLocation()
    {
        return $this->payload['config_location'];
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

        if (! is_dir($aPayload['config_location'])) {
            throw new \InvalidArgumentException("Config location {$aPayload['config_location']} is not a valid directory");
        }
    }
}
 
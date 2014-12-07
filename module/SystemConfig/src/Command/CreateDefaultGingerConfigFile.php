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

use Prooph\ServiceBus\Command;

/**
 * Class CreateDefaultGingerConfigFile
 *
 * @package SystemConfig\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class CreateDefaultGingerConfigFile extends Command
{
    /**
     * @param string $configLocation
     * @return CreateDefaultGingerConfigFile
     * @throws \InvalidArgumentException
     */
    public static function in($configLocation)
    {
        if (! is_string($configLocation)) {
            throw new \InvalidArgumentException("Config location must be string, but type " . gettype($configLocation) . " given");
        }

        if (! is_dir($configLocation)) {
            throw new \InvalidArgumentException("Config location $configLocation is not a valid directory");
        }

        return new self(__CLASS__, ['config_location' => $configLocation]);
    }

    /**
     * @return string
     */
    public function configLocation()
    {
        return $this->payload['config_location'];
    }
}
 
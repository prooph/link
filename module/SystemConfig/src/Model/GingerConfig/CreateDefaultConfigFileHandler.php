<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 21:33
 */

namespace SystemConfig\Model\GingerConfig;

use SystemConfig\Command\CreateDefaultGingerConfigFile;
use SystemConfig\Model\ConfigWriter;
use SystemConfig\Model\GingerConfig;

/**
 * Class CreateDefaultConfigFileHandler
 *
 * @package SystemConfig\Model\GingerConfig
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class CreateDefaultConfigFileHandler 
{
    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @param ConfigWriter $configWriter
     */
    public function __construct(ConfigWriter $configWriter)
    {
        $this->configWriter = $configWriter;
    }

    /**
     * @param CreateDefaultGingerConfigFile $command
     * @throws \RuntimeException
     */
    public function handleCreateDefaultGingerConfigFile(CreateDefaultGingerConfigFile $command)
    {
        $gingerConfig = GingerConfig::initializeWithDefaults();

        if (! is_writable($command->configLocation())) {
            throw new \RuntimeException(sprintf('Config location %s is not writable', $command->configLocation()));
        }

        $this->configWriter->writeNewConfigToDirectory(
            $gingerConfig->toArray(),
            $command->configLocation() . '/' . $gingerConfig->configFileName()
        );
    }
}
 
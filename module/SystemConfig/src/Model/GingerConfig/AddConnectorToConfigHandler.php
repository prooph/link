<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.01.15 - 21:42
 */

namespace SystemConfig\Model\GingerConfig;

use SystemConfig\Command\AddConnectorToConfig;
use SystemConfig\Model\GingerConfig;

/**
 * Class AddConnectorToConfigHandler
 *
 * @package SystemConfig\Model\GingerConfig
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class AddConnectorToConfigHandler extends SystemConfigChangesHandler
{
    public function handle(AddConnectorToConfig $command)
    {
        $gingerConfig = GingerConfig::initializeFromConfigLocation($command->configLocation());

        $gingerConfig->addConnector(
            $command->connectorId(),
            $command->connectorName(),
            $command->allowedMessage(),
            $command->allowedTypes(),
            $this->configWriter,
            $command->additionalData()
        );

        $this->publishChangesOf($gingerConfig);
    }
}
 
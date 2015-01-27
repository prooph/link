<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.01.15 - 02:25
 */

namespace SystemConfig\Model\GingerConfig;

use SystemConfig\Command\ChangeConnectorConfig;
use SystemConfig\Model\GingerConfig;

/**
 * Class ChangeConnectorConfigHandler
 *
 * @package SystemConfig\Model\GingerConfig
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ChangeConnectorConfigHandler extends SystemConfigChangesHandler
{
    public function handle(ChangeConnectorConfig $command)
    {
        $gingerConfig = GingerConfig::initializeFromConfigLocation($command->configLocation());

        $gingerConfig->changeConnector($command->connectorId(), $command->connectorConfig(), $this->configWriter);

        $this->publishChangesOf($gingerConfig);
    }
}
 
<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.01.15 - 02:25
 */

namespace SystemConfig\Model\ProcessingConfig;

use SystemConfig\Command\ChangeConnectorConfig;
use SystemConfig\Model\ProcessingConfig;

/**
 * Class ChangeConnectorConfigHandler
 *
 * @package SystemConfig\Model\ProcessingConfig
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ChangeConnectorConfigHandler extends SystemConfigChangesHandler
{
    public function handle(ChangeConnectorConfig $command)
    {
        $processingConfig = ProcessingConfig::initializeFromConfigLocation($command->configLocation());

        $processingConfig->changeConnector($command->connectorId(), $command->connectorConfig(), $this->configWriter);

        $this->publishChangesOf($processingConfig);
    }
}
 
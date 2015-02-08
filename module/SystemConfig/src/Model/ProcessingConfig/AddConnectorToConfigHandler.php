<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.01.15 - 21:42
 */

namespace SystemConfig\Model\ProcessingConfig;

use SystemConfig\Command\AddConnectorToConfig;
use SystemConfig\Model\ProcessingConfig;

/**
 * Class AddConnectorToConfigHandler
 *
 * @package SystemConfig\Model\ProcessingConfig
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class AddConnectorToConfigHandler extends SystemConfigChangesHandler
{
    public function handle(AddConnectorToConfig $command)
    {
        $processingConfig = ProcessingConfig::initializeFromConfigLocation($command->configLocation());

        $processingConfig->addConnector(
            $command->connectorId(),
            $command->connectorName(),
            $command->allowedMessage(),
            $command->allowedTypes(),
            $this->configWriter,
            $command->additionalData()
        );

        $this->publishChangesOf($processingConfig);
    }
}
 
<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/8/14 - 10:53 PM
 */
namespace SystemConfig\Model\ProcessingConfig;

use Prooph\ServiceBus\EventBus;
use SystemConfig\Command\ChangeNodeName;
use SystemConfig\Model\ConfigWriter;
use SystemConfig\Model\ProcessingConfig;

/**
 * Class ChangeNodeNameHandler
 *
 * @package SystemConfig\Model\ProcessingConfig
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class ChangeNodeNameHandler extends SystemConfigChangesHandler
{
    /**
     * @param ChangeNodeName $command
     */
    public function handle(ChangeNodeName $command)
    {
        $processingConfig = ProcessingConfig::initializeFromConfigLocation($command->configLocation());

        $processingConfig->changeNodeName($command->newNodeName(), $this->configWriter);

        $this->publishChangesOf($processingConfig);
    }
} 
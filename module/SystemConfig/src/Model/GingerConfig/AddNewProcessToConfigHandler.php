<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/14/14 - 10:05 PM
 */
namespace SystemConfig\Model\GingerConfig;

use Prooph\ServiceBus\EventBus;
use SystemConfig\Command\AddNewProcessToConfig;
use SystemConfig\Model\ConfigWriter;
use SystemConfig\Model\GingerConfig;

/**
 * Class CreateProcessHandler
 *
 * Adds a new process definition to ginger config
 *
 * @package SystemConfig\Model\GingerConfig
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class AddNewProcessToConfigHandler
{
    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @param ConfigWriter $configWriter
     * @param EventBus $eventBus
     */
    public function __construct(ConfigWriter $configWriter, EventBus $eventBus)
    {
        $this->configWriter = $configWriter;
        $this->eventBus = $eventBus;
    }

    /**
     * @param AddNewProcessToConfig $command
     */
    public function handle(AddNewProcessToConfig $command)
    {
        $gingerConfig = GingerConfig::initializeFromConfigLocation($command->configLocation());

        $gingerConfig->addProcess(
            $command->processName(),
            $command->processType(),
            $command->startMessage(),
            $command->tasks(),
            $this->configWriter
        );

        foreach ($gingerConfig->popRecordedEvents() as $recordedEvent) $this->eventBus->dispatch($recordedEvent);
    }
} 
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 30.12.14 - 18:13
 */

namespace SystemConfig\Model\GingerConfig;
use Prooph\ServiceBus\EventBus;
use SystemConfig\Command\ChangeProcessConfig;
use SystemConfig\Model\ConfigWriter;
use SystemConfig\Model\GingerConfig;

/**
 * Class ChangeProcessConfigHandler
 *
 * @package SystemConfig\Model\GingerConfig
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ChangeProcessConfigHandler 
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
     * @param ChangeProcessConfig $command
     */
    public function handle(ChangeProcessConfig $command)
    {
        $gingerConfig = GingerConfig::initializeFromConfigLocation($command->configLocation());

        $gingerConfig->replaceProcessTriggeredBy($command->startMessage(), $command->processConfig(), $this->configWriter);

        foreach ($gingerConfig->popRecordedEvents() as $recordedEvent) $this->eventBus->dispatch($recordedEvent);
    }
}
 
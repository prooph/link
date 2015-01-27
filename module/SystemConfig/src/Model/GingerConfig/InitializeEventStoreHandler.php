<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 02.01.15 - 12:49
 */

namespace SystemConfig\Model\GingerConfig;

use Prooph\ServiceBus\EventBus;
use SystemConfig\Command\InitializeEventStore;
use SystemConfig\Model\ConfigWriter;
use SystemConfig\Model\EventStoreConfig;

/**
 * Class InitializeEventStoreHandler
 *
 * Triggers the initialization of the event store
 *
 * @package SystemConfig\Model\GingerConfig
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class InitializeEventStoreHandler extends SystemConfigChangesHandler
{
    /**
     * @param InitializeEventStore $command
     */
    public function handle(InitializeEventStore $command)
    {
        $esConfig = EventStoreConfig::initializeWithSqliteDb(
            $command->sqliteDbFile(),
            $command->eventStoreConfigLocation(),
            $this->configWriter
        );

        foreach ($esConfig->popRecordedEvents() as $recordedEvent) $this->eventBus->dispatch($recordedEvent);
    }
}
 
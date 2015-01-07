<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 02.01.15 - 17:00
 */

namespace SystemConfig\Model\GingerConfig;
use Prooph\ServiceBus\EventBus;
use SystemConfig\Command\UndoSystemSetUp;
use SystemConfig\Model\EventStoreConfig;
use SystemConfig\Model\GingerConfig;

/**
 * Class UndoSystemSetUpHandler
 *
 * @package SystemConfig\Model\GingerConfig
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class UndoSystemSetUpHandler extends SystemConfigChangesHandler
{
    /**
     * @param UndoSystemSetUp $command
     */
    public function handle(UndoSystemSetUp $command)
    {
        $this->eventBus->dispatch(GingerConfig::removeConfig($command->gingerConfigLocation()));
        $this->eventBus->dispatch(EventStoreConfig::undoEventStoreSetUp($command->eventStoreConfigLocation(), $command->sqliteDbFile()));
    }
}
 
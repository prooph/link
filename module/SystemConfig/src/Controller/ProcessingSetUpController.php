<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 21:01
 */

namespace SystemConfig\Controller;

use Application\Service\AbstractActionController;
use Application\SharedKernel\SqliteDbFile;
use SystemConfig\Command\CreateDefaultProcessingConfigFile;
use SystemConfig\Command\InitializeEventStore;
use SystemConfig\Command\UndoSystemSetUp;
use SystemConfig\Definition;
use Application\SharedKernel\ConfigLocation;

/**
 * Class ProcessingSetUpController
 *
 * @package SystemConfig\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ProcessingSetUpController extends AbstractActionController
{
    /**
     * Runs the initial set up of the processing system
     */
    public function runAction()
    {
        try {
            $this->commandBus->dispatch(CreateDefaultProcessingConfigFile::in(ConfigLocation::fromPath(Definition::getSystemConfigDir())));

            $sqliteDbFile = SqliteDbFile::initializeFromDist(Definition::getEventStoreSqliteDbFile());

            $esConfigLocation = ConfigLocation::fromPath(Definition::getSystemConfigDir());

            $this->commandBus->dispatch(InitializeEventStore::setUpWithSqliteDbAdapter($sqliteDbFile, $esConfigLocation));

        } catch (\Exception $ex) {
            $this->commandBus->dispatch(UndoSystemSetUp::removeConfigs(
                Definition::getSystemConfigDir(),
                Definition::getSystemConfigDir(),
                Definition::getEventStoreSqliteDbFile()
            ));

            throw $ex;
        }

        return $this->redirect()->toRoute('system_config');
    }
}
 
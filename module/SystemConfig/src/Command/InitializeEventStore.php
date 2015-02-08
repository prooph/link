<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 01.01.15 - 23:37
 */

namespace SystemConfig\Command;

use Application\Command\SystemCommand;
use Application\SharedKernel\ConfigLocation;
use Application\SharedKernel\SqliteDbFile;

/**
 * Class InitializeEventStore
 *
 * Triggers initialization of the event store. Default set up uses a local sqlite db
 *
 * @package SystemConfig\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class InitializeEventStore extends SystemCommand
{
    /**
     * @param SqliteDbFile $sqliteDbFile
     * @param ConfigLocation $eventStoreConfigLocation
     * @return \SystemConfig\Command\InitializeEventStore
     */
    public static function setUpWithSqliteDbAdapter(SqliteDbFile $sqliteDbFile, ConfigLocation $eventStoreConfigLocation)
    {
        return new self(
            __CLASS__,
            [
                'sqlite_db_file' => $sqliteDbFile->toString(),
                'es_config_location' => $eventStoreConfigLocation->toString()
            ]
        );
    }

    /**
     * @return ConfigLocation
     */
    public function eventStoreConfigLocation()
    {
        return ConfigLocation::fromPath($this->payload['es_config_location']);
    }

    /**
     * @return SqliteDbFile
     */
    public function sqliteDbFile()
    {
        return SqliteDbFile::fromFilename($this->payload['sqlite_db_file']);
    }

    protected function assertPayload($aPayload = null)
    {
        if (! is_array($aPayload)) throw new \InvalidArgumentException("Payload must be an array");
        if (! array_key_exists("sqlite_db_file",$aPayload)) throw new \InvalidArgumentException("Sqlite db file missing");
        if (! array_key_exists("es_config_location",$aPayload)) throw new \InvalidArgumentException("Event store config location missing");
    }
}
 
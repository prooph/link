<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 02.01.15 - 16:35
 */

namespace SystemConfig\Event;

use Application\Event\SystemChanged;
use Application\SharedKernel\ConfigLocation;
use Application\SharedKernel\SqliteDbFile;

/**
 * Event EventStoreWasInitialized
 *
 * @package SystemConfig\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class EventStoreWasInitialized extends SystemChanged
{
    /**
     * @param SqliteDbFile $sqliteDbFile
     * @param ConfigLocation $configLocation
     * @param $fileName
     * @return EventStoreWasInitialized
     */
    public static function withSqliteDb(SqliteDbFile $sqliteDbFile, ConfigLocation $configLocation, $fileName)
    {
        return self::occur([
            'config_file' => $configLocation->toString() . DIRECTORY_SEPARATOR . $fileName,
            'sqlite_db_file' => $sqliteDbFile->toString()
        ]);
    }

    /**
     * @return string
     */
    public function configFile()
    {
        return $this->payload['config_file'];
    }

    /**
     * @return string
     */
    public function sqliteDbFile()
    {
        return $this->payload['sqlite_db_file'];
    }
}
 
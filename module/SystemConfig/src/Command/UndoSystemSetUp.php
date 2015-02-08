<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 02.01.15 - 16:56
 */

namespace SystemConfig\Command;

use Application\Command\SystemCommand;
use Application\SharedKernel\ConfigLocation;
use Application\SharedKernel\SqliteDbFile;

/**
 * Class UndoSystemSetUp
 *
 * @package SystemConfig\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class UndoSystemSetUp extends SystemCommand
{
    /**
     * @param string $systemConfigLocation
     * @param string $eventStoreConfigLocation
     * @param string $sqliteDbFile
     * @return UndoSystemSetUp
     */
    public static function removeConfigs($systemConfigLocation, $eventStoreConfigLocation, $sqliteDbFile)
    {
        return new self(__CLASS__, ['processing_config_location' => $systemConfigLocation, 'es_config_location' => $eventStoreConfigLocation, 'sqlite_db_file' => $sqliteDbFile]);
    }

    /**
     * @return string
     */
    public function processingConfigLocation()
    {
        return $this->payload['processing_config_location'];
    }

    /**
     * @return string
     */
    public function eventStoreConfigLocation()
    {
        return $this->payload['es_config_location'];
    }

    /**
     * @return string
     */
    public function sqliteDbFile()
    {
        return $this->payload['sqlite_db_file'];
    }

    protected function assertPayload($aPayload = null)
    {
        if (! is_array($aPayload)) throw new \InvalidArgumentException("Payload must be an array");
        if (! array_key_exists("processing_config_location",$aPayload)) throw new \InvalidArgumentException("processing_config_location missing");
        if (! array_key_exists("es_config_location",$aPayload)) throw new \InvalidArgumentException("Event store config location missing");
        if (! array_key_exists("sqlite_db_file",$aPayload)) throw new \InvalidArgumentException("sqlite_db_file missing");
    }
}
 
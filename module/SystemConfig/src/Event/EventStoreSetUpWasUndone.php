<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 02.01.15 - 17:20
 */

namespace SystemConfig\Event;

use Application\Event\SystemChanged;
use Application\SharedKernel\ConfigLocation;

/**
 * Class EventStoreSetUpWasUndone
 *
 * @package SystemConfig\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class EventStoreSetUpWasUndone extends SystemChanged
{
    /**
     * @param string $fileName
     * @return static
     */
    public static function in($fileName)
    {
        return self::occur(['config_file' => $fileName]);
    }

    /**
     * @return string
     */
    public function configFile()
    {
        return $this->toPayloadReader()->stringValue('config_file');
    }
}
 
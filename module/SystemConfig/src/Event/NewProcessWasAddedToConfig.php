<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/14/14 - 11:23 PM
 */
namespace SystemConfig\Event;

use Application\Event\SystemChanged;

/**
 * Class NewProcessWasAddedToConfig
 *
 * @package SystemConfig\Event
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class NewProcessWasAddedToConfig extends SystemChanged
{
    public static function withDefinition($startMessage, array $processConfig)
    {
        return self::occur(["start_message" => $startMessage, "process_config" => $processConfig]);
    }

    /**
     * @return string
     */
    public function startMessage()
    {
        return $this->payload['start_message'];
    }

    /**
     * @return array
     */
    public function processConfig()
    {
        return $this->payload['process_config'];
    }
} 
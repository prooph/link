<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.01.15 - 22:50
 */

namespace SystemConfig\Event;

use Application\Event\SystemChanged;

/**
 * Class ConnectorWasAddedToConfig
 *
 * @package SystemConfig\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ConnectorWasAddedToConfig extends SystemChanged
{
    /**
     * @param $connectorId
     * @param array $connectorConfig
     * @return ConnectorWasAddedToConfig
     */
    public static function withDefinition($connectorId, array $connectorConfig)
    {
        return self::occur(['connector_id' => $connectorId, 'connector_config' => $connectorConfig]);
    }

    /**
     * @return string
     */
    public function connectorId()
    {
        return $this->toPayloadReader()->stringValue("connector_id");
    }

    /**
     * @return array
     */
    public function connectorConfig()
    {
        return $this->toPayloadReader()->arrayValue("connector_config");
    }
}
 
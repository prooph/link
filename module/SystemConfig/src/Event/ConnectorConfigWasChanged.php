<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.01.15 - 02:31
 */

namespace SystemConfig\Event;

use Application\Event\SystemChanged;

/**
 * Class ConnectorConfigWasChanged
 *
 * @package SystemConfig\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ConnectorConfigWasChanged extends SystemChanged
{
    /**
     * @param array $newConfig
     * @param array $oldConfig
     * @param string $connectorId
     * @return ConnectorConfigWasChanged
     */
    public static function to(array $newConfig, array $oldConfig, $connectorId)
    {
        return self::occur([
            'connector_id' => $connectorId,
            'new_config' => $newConfig,
            'old_config' => $oldConfig
        ]);
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
    public function newConnectorConfig()
    {
        return $this->toPayloadReader()->arrayValue("new_config");
    }

    /**
     * @return array
     */
    public function oldConnectorConfig()
    {
        return $this->toPayloadReader()->arrayValue("old_config");
    }
}
 
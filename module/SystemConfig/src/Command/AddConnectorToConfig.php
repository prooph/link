<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.01.15 - 21:33
 */

namespace SystemConfig\Command;
use Application\Command\SystemCommand;
use Application\SharedKernel\ConfigLocation;

/**
 * Command AddConnectorToConfig
 *
 * @package SystemConfig\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class AddConnectorToConfig extends SystemCommand
{
    /**
     * @param $connectorId
     * @param $connectorName
     * @param array $allowedMessages
     * @param array $allowedTypes
     * @param ConfigLocation $configLocation
     * @param array $additionalData
     * @return AddConnectorToConfig
     */
    public static function fromDefinition($connectorId, $connectorName, array $allowedMessages, array $allowedTypes, ConfigLocation $configLocation, array $additionalData = array())
    {
        return new self(
            __CLASS__,
            [
                'connector_id'     => $connectorId,
                'connector_name'   => $connectorName,
                'allowed_messages' => $allowedMessages,
                'allowed_types'    => $allowedTypes,
                'additional_data'  => $additionalData,
                'config_location'  => $configLocation->toString(),
            ]
        );
    }

    /**
     * @return string
     */
    public function connectorId()
    {
        return $this->payload['connector_id'];
    }

    /**
     * @return string
     */
    public function connectorName()
    {
        return $this->payload['connector_name'];
    }

    /**
     * @return ConfigLocation
     */
    public function configLocation()
    {
        return ConfigLocation::fromPath($this->payload['config_location']);
    }

    /**
     * @return array
     */
    public function allowedMessage()
    {
        return $this->payload['allowed_messages'];
    }

    /**
     * @return array
     */
    public function allowedTypes()
    {
        return $this->payload['allowed_types'];
    }

    /**
     * @return array
     */
    public function additionalData()
    {
        return $this->payload['additional_data'];
    }

    protected function assertPayload($aPayload = null)
    {
        if (! is_array($aPayload)) throw new \InvalidArgumentException("Payload must be an array");
        if (! array_key_exists("connector_id",$aPayload)) throw new \InvalidArgumentException("Connector id missing");
        if (! array_key_exists("connector_name",$aPayload)) throw new \InvalidArgumentException("Connector name missing");
        if (! array_key_exists("config_location",$aPayload)) throw new \InvalidArgumentException("Config location missing");
        if (! array_key_exists("allowed_messages",$aPayload)) throw new \InvalidArgumentException("Allowed messages missing");
        if (! is_array($aPayload['allowed_messages'])) throw new \InvalidArgumentException("Allowed messages must be an array");
        if (! array_key_exists("allowed_types",$aPayload)) throw new \InvalidArgumentException("Allowed types missing");
        if (! is_array($aPayload['allowed_types'])) throw new \InvalidArgumentException("Allowed types must be an array");
        if (! array_key_exists("additional_data",$aPayload)) throw new \InvalidArgumentException("Additional data missing");
        if (! is_array($aPayload['additional_data'])) throw new \InvalidArgumentException("Additional data must be an array");
    }
}
 
<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.01.15 - 16:51
 */

namespace FileConnector\FileManager;
use Prooph\Processing\Message\MessageNameUtils;
use Rhumsaa\Uuid\Uuid;

/**
 * Class FileGatewayTranslator
 *
 * @package FileConnector\FileManager
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FileGatewayTranslator
{
    public static function generateConnectorId()
    {
        return "filegateway:::" . Uuid::uuid4();
    }

    /**
     * @param array $connectorData
     * @internal param string $connectorId
     * @return array
     */
    public static function translateToClient(array $connectorData)
    {
        $connectorData['writable'] = in_array(MessageNameUtils::PROCESS_DATA, $connectorData['allowed_messages']);
        $connectorData['readable'] = in_array(MessageNameUtils::COLLECT_DATA, $connectorData['allowed_messages']);

        unset($connectorData['allowed_messages']);

        $connectorData['processing_type'] = $connectorData['allowed_types'][0];

        unset($connectorData['allowed_types']);

        unset($connectorData['ui_metadata_key']);

        return $connectorData;
    }

    /**
     * @param array $connectorData
     * @return array
     */
    public static function translateFromClient(array $connectorData)
    {
        if (isset($connectorData['id'])) unset($connectorData['id']);

        $connectorData['allowed_messages'] = [];

        if ($connectorData['writable']) $connectorData['allowed_messages'][] = MessageNameUtils::PROCESS_DATA;
        if ($connectorData['readable']) $connectorData['allowed_messages'][] = MessageNameUtils::COLLECT_DATA;

        unset($connectorData['writable']);
        unset($connectorData['readable']);

        $connectorData['allowed_types'] = [$connectorData['processing_type']];

        unset($connectorData['processing_type']);

        return $connectorData;
    }
}
 
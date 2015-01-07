<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.01.15 - 16:51
 */

namespace FileConnector\FileManager;
use Ginger\Message\MessageNameUtils;

/**
 * Class FileConnectorTranslator
 *
 * @package FileConnector\FileManager
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FileConnectorTranslator 
{
    public static function generateConnectorId(array $connectorData)
    {
        return "fileconnector:::" . MessageNameUtils::normalize($connectorData['name']);
    }

    /**
     * @param array $connectorData
     * @return array
     */
    public static function translateForClient(array $connectorData)
    {
        $connectorData['id'] = self::generateConnectorId($connectorData);
        $connectorData['writable'] = in_array("process-data", $connectorData['allowed_messages']);
        $connectorData['readable'] = in_array("collect-data", $connectorData['allowed_messages']);

        unset($connectorData['allowed_messages']);

        $connectorData['data_type'] = $connectorData['allowed_types'][0];

        unset($connectorData['allowed_types']);

        return $connectorData;
    }
}
 
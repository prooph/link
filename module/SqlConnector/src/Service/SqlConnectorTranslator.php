<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 24.01.15 - 21:24
 */

namespace SqlConnector\Service;

use Rhumsaa\Uuid\Uuid;

/**
 * Class SqlConnectorTranslator
 *
 * @package SqlConnector\SqlManager
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class SqlConnectorTranslator 
{
    public static function generateConnectorId()
    {
        return "sqlconnector:::" . Uuid::uuid4();
    }

    public static function translateToClient(array $connectorData)
    {
        return $connectorData;
    }
}
 
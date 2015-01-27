<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 01:09
 */

namespace SqlConnectorTest\DataType;

use Application\DataType\SqlConnector\TableRow;
use Ginger\Type\Description\Description;
use Ginger\Type\Description\NativeType;
use Ginger\Type\Integer;
use Ginger\Type\String;

/**
 * Class TestUser
 *
 * @package SqlConnectorTest\DataType
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class TestUser extends TableRow
{

    /**
     * @return array[propertyName => Prototype]
     */
    public static function getPropertyPrototypes()
    {
        return [
            'id' => Integer::prototype(),
            'name' => String::prototype(),
            'age' => Integer::prototype()
        ];
    }

    /**
     * @return Description
     */
    public static function buildDescription()
    {
        return new Description('TestUser', NativeType::DICTIONARY, true, 'id');
    }
}
 
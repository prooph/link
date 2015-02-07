<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 01:09
 */

namespace SqlConnectorTest\DataType;

use Application\DataType\SqlConnector\TableRow;
use Prooph\Processing\Type\Description\Description;
use Prooph\Processing\Type\Description\NativeType;
use Prooph\Processing\Type\Integer;
use Prooph\Processing\Type\IntegerOrNull;
use Prooph\Processing\Type\String;

/**
 * Class TestUser
 *
 * @package SqlConnectorTest\DataType
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class TestUser extends TableRow
{
    /**
     * @var array list of doctrine types indexed by property name
     */
    protected static $propertyDbTypes = [
        'id' => 'integer',
        'name' => 'string',
        'age' => 'integer',

    ];

    /**
     * @var string Doctrine database platform class
     */
    protected static $platformClass = 'Doctrine\DBAL\Platforms\SqlitePlatform';

    /**
     * @return array[propertyName => Prototype]
     */
    public static function getPropertyPrototypes()
    {
        return [
            //Id can be null, because id column is configured to be AUTOINCREMENT
            'id' => IntegerOrNull::prototype(),
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
 
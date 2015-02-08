<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 01:11
 */

namespace FileConnectorTest\DataType;

use Prooph\Processing\Type\AbstractCollection;
use Prooph\Processing\Type\Description\Description;
use Prooph\Processing\Type\Description\NativeType;
use Prooph\Processing\Type\Prototype;

/**
 * Class TestUserCollection
 *
 * @package SqlConnectorTest\DataType
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class TestUserCollection extends AbstractCollection
{
    /**
     * Returns the prototype of the items type
     *
     * A collection has always one property with name item representing the type of all items in the collection.
     *
     * @return Prototype
     */
    public static function itemPrototype()
    {
        return TestUser::prototype();
    }

    /**
     * @return Description
     */
    public static function buildDescription()
    {
        return new Description('TestUserCollection', NativeType::COLLECTION, false);
    }
}
 
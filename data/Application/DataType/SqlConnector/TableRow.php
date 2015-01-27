<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 01:43
 */

namespace Application\DataType\SqlConnector;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Ginger\Type\AbstractDictionary;
use Ginger\Type\Description\NativeType;

/**
 * Class TableRow
 *
 * @package SqlConnector\DataType
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
abstract class TableRow extends AbstractDictionary
{
    /**
     * @var array list of doctrine types indexed by property name
     */
    protected static $propertyDbTypes = [];

    /**
     * @var string Doctrine database platform class
     */
    protected static $platformClass;

    /**
     * @var AbstractPlatform[] cache
     */
    private static $platforms = [];

    /**
     * @param array $row
     * @return static
     */
    public static function fromDatabaseRow(array $row)
    {
        foreach ($row as $property => $value) {
            $newProperty = static::toNativePropertyName($property);

            $row[$newProperty] = static::toNativeValue($newProperty, $value);

            if ($newProperty != $property) unset($row[$property]);
        }

        return static::fromNativeValue($row);
    }

    /**
     * Override method to implement custom property translation
     *
     * @param string $property
     * @return string
     */
    public static function toNativePropertyName($property)
    {
        return $property;
    }

    /**
     * @param string $property
     * @return string
     */
    public static function toDbColumnName($property)
    {
        return $property;
    }

    public static function toNativeValue($property, $value)
    {
        if (isset(static::$propertyDbTypes[$property]) && static::$platformClass) {
            $doctrineType = Type::getType(static::$propertyDbTypes[$property]);
            return $doctrineType->convertToPHPValue($value, self::getPlatform(static::$platformClass));
        }

        $typeProperties = static::prototype()->typeProperties();

        if (! isset($typeProperties[$property])) throw new \InvalidArgumentException(sprintf("Column %s can not be mapped to a property of ginger type %s", $property, __CLASS__));

        $typeProperty = $typeProperties[$property];

        switch ($typeProperty->typePrototype()->typeDescription()->nativeType()) {
            case NativeType::INTEGER:
                return (int)$value;
            case NativeType::BOOLEAN:
                return (bool)$value;
            case NativeType::FLOAT:
                return (float)$value;
            case NativeType::DATETIME:
                return new \DateTime($value);
            default:
                return $value;
        }
    }

    /**
     * @param string $platformClass
     * @return AbstractPlatform
     */
    protected static function getPlatform($platformClass)
    {
        if (! isset(self::$platforms[$platformClass])) {
            self::$platforms[$platformClass] = new $platformClass();
        }

        return self::$platforms[$platformClass];
    }
}
 
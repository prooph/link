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

use Assert\Assertion;
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
     * @throws \Exception
     * @throws \Ginger\Type\Exception\InvalidTypeException
     * @return static
     */
    public static function fromDatabaseRow(array $row)
    {
        foreach ($row as $property => $value) {
            $newProperty = static::toNativePropertyName($property);

            $row[$newProperty] = static::toNativeValue($newProperty, $value);

            if ($newProperty != $property) unset($row[$property]);
        }

        try {
            return static::fromNativeValue($row);
        } catch (\Exception $e) {
            if (static::buildDescription()->hasIdentifier()) {
                $identifier = static::buildDescription()->identifierName();

                if (isset($row[$identifier])) {
                    throw new \RuntimeException(
                        sprintf(
                            'Failed to process data set: %s = %s. Reason: %s',
                            $identifier,
                            $row[$identifier],
                            $e->getMessage()
                        ),
                        null,
                        $e
                    );
                }
            }

            throw $e;
        }

    }

    /**
     * If column does not directly match with a property name a case insensitive compare is performed to
     * detect the correct property name.
     *
     * Override method to implement custom property translation
     *
     * @param string $property
     * @return string
     */
    public static function toNativePropertyName($property)
    {
        $propertyNames = array_keys(static::getPropertyPrototypes());

        if (! in_array($property, $propertyNames)) {
            foreach ($propertyNames as $propertyName) {
                if (strtolower($propertyName) === strtolower($property)) {
                    return $propertyName;
                }
            }
        }

        return $property;
    }

    /**
     * @param string $property
     * @return string
     */
    public static function toDbColumnName($property)
    {
        $dbColumnNames = array_keys(static::$propertyDbTypes);

        if (! in_array($property, $dbColumnNames)) {
            foreach ($dbColumnNames as $columnName) {
                if (strtolower($columnName) === strtolower($property)) {
                    return $columnName;
                }
            }
        }

        return $property;
    }

    /**
     * @param string $property
     * @return string|null
     */
    public static function getDbTypeForProperty($property)
    {
        $dbColumn = static::toDbColumnName($property);

        if (isset(static::$propertyDbTypes[$dbColumn])) return static::$propertyDbTypes[$dbColumn];

        return null;
    }

    public static function toNativeValue($property, $value)
    {
        if (isset(static::$propertyDbTypes[$property]) && static::$platformClass) {
            $doctrineType = Type::getType(static::$propertyDbTypes[$property]);
            $convertedValue = $doctrineType->convertToPHPValue($value, self::getPlatform(static::$platformClass));

            //Doctrine converts empty strings to null, so we can not fully rely on doctrine's conversion
            if (! is_null($convertedValue)) return $convertedValue;
        }

        if (is_null($value)) {
            return null;
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
            case NativeType::STRING:
                return (string)$value;
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
 
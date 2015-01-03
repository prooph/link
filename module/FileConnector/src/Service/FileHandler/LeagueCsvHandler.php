<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 21:00
 */

namespace FileConnector\Service\FileHandler;

use FileConnector\Service\FileHandler;
use Ginger\Type\Description\NativeType;
use Ginger\Type\Prototype;
use Ginger\Type\Type;
use League\Csv\Reader;

/**
 * Class LeagueCsvHandler
 *
 * @package FileConnector\Service\FileHandler
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class LeagueCsvHandler implements FileHandler
{
    /**
     * Use League\Csv\Reader to fetch data from csv file
     *
     * The reader checks if the first row of the file is the header row otherwise it uses the property names
     * of the prototype item to match columns. In this case the order in the file must be the same as defined in the prototype item.
     * The second scenario should be avoided because it can lead to silent errors.
     *
     * @param string $filename
     * @param Prototype $prototype
     * @return array
     * @throws \Exception
     */
    public function readDataForType($filename, Prototype $prototype)
    {
        if ($prototype->typeDescription()->nativeType() !== NativeType::COLLECTION) throw new \InvalidArgumentException('The CsvReader can only handle collections');

        $itemPrototype = $prototype->propertiesOfType()['item']->typePrototype();

        $propertyNames = array_keys($itemPrototype->propertiesOfType());

        $reader = Reader::createFromPath($filename);

        $firstRow = $reader->fetchOne();

        $offset_or_keys = 0;

        if ($this->isValidKeysRow($firstRow, $propertyNames)) {
            $reader->addFilter(function ($row, $rowIndex) {
                return is_array($row) && $rowIndex != 0;
            });
        } else {
            $offset_or_keys = $propertyNames;
        }

        return array_map(function($row) use ($itemPrototype) {
            return $this->convertToItemData($row, $itemPrototype);
        }, $reader->fetchAssoc($offset_or_keys));
    }

    /**
     * @param string $filename
     * @param Type $type
     * @return void
     * @throws \Exception
     */
    public function writeDataOfType($filename, Type $type)
    {
        // TODO: Implement writeDataOfType() method.
    }

    /**
     * @param array $row
     * @param array $itemProperties
     * @return bool
     */
    private function isValidKeysRow(array $row, array $itemProperties)
    {
        $validKeysInRow = array_filter($row, function($key) use ($itemProperties) { return in_array($key, $itemProperties);});

        return count($row) === count($validKeysInRow);
    }

    /**
     * @param array $csvRow
     * @param Prototype $itemPrototype
     * @throws \InvalidArgumentException
     */
    protected function convertToItemData(array $csvRow, Prototype $itemPrototype)
    {
        $itemProperties = $itemPrototype->propertiesOfType();

        foreach ($csvRow as $propertyName => $propertyValue) {

            if (! isset($itemProperties[$propertyName])) throw new \InvalidArgumentException(sprintf('Csv row property %s is not a known item property. Valid properties are %s', $propertyName, implode(', ', array_keys($itemProperties))));

            $propertyType = $itemProperties[$propertyName]->typePrototype()->of();

            $csvRow[$propertyName] = $propertyType::fromString((string)$csvRow[$propertyName]);
        }

        $itemType = $itemPrototype->of();

        return $itemType::fromNativeValue($csvRow);
    }
}
 
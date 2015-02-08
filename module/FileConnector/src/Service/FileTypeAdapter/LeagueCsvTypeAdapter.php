<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 21:00
 */

namespace FileConnector\Service\FileTypeAdapter;

use FileConnector\Service\FileTypeAdapter;
use Prooph\Processing\Type\Description\NativeType;
use Prooph\Processing\Type\Prototype;
use Prooph\Processing\Type\Type;
use League\Csv\Reader;
use League\Csv\Writer;

/**
 * Class LeagueCsvTypeAdapter
 *
 * @package FileConnector\Service\FileTypeAdapter
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class LeagueCsvTypeAdapter implements FileTypeAdapter
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
     * @param array $metadata
     * @throws \InvalidArgumentException
     * @return array
     */
    public function readDataForType($filename, Prototype $prototype, array &$metadata = [])
    {
        if ($prototype->typeDescription()->nativeType() !== NativeType::COLLECTION) throw new \InvalidArgumentException('The CsvReader can only handle collections');

        $itemPrototype = $prototype->typeProperties()['item']->typePrototype();

        $propertyNames = array_keys($itemPrototype->typeProperties());

        $reader = Reader::createFromPath($filename);

        if (array_key_exists('delimiter', $metadata))     $reader->setDelimiter($metadata['delimiter']);
        if (array_key_exists('enclosure', $metadata))     $reader->setEnclosure($metadata['enclosure']);
        if (array_key_exists('escape', $metadata))        $reader->setEscape($metadata['escape']);
        if (array_key_exists('file_encoding', $metadata)) $reader->setEncodingFrom($metadata['file_encoding']);

        $firstRow = $reader->fetchOne();

        $offset_or_keys = 0;

        $iteratorFilters = [];

        if ($this->isValidKeysRow($firstRow, $propertyNames)) {
            $iteratorFilters[] = function ($row, $rowIndex) {
                return $rowIndex != 0;
            };
        } else {
            $offset_or_keys = $propertyNames;
        }

        //Filter empty rows
        $iteratorFilters[] = function($row) {
            if (! is_array($row)) return false;
            if (empty($row)) return false;
            if (count($row) === 1) {
                $value = current($row);
                return ! empty($value);
            }

            return true;
        };

        foreach($iteratorFilters as $iteratorFilter) $reader->addFilter($iteratorFilter);

        $metadata['total_items'] = $reader->each(function() { return true; });

        if (array_key_exists('offset', $metadata)) $reader->setOffset($metadata['offset']);
        if (array_key_exists('limit', $metadata))  $reader->setLimit($metadata['limit']);

        foreach($iteratorFilters as $iteratorFilter) $reader->addFilter($iteratorFilter);

        return array_map(function($row) use ($itemPrototype) {
            return $this->convertToItemData($row, $itemPrototype);
        }, $reader->fetchAssoc($offset_or_keys));
    }

    /**
     * @param string $filename
     * @param Type $type
     * @param array $metadata
     * @return void
     */
    public function writeDataOfType($filename, Type $type, array &$metadata = [])
    {
        $writeHeader = ! file_exists($filename);

        $writer = Writer::createFromPath($filename, 'a');

        if (array_key_exists('delimiter', $metadata)) $writer->setDelimiter($metadata['delimiter']);
        if (array_key_exists('enclosure', $metadata)) $writer->setEnclosure($metadata['enclosure']);
        if (array_key_exists('escape', $metadata))    $writer->setEscape($metadata['escape']);
        if (array_key_exists('newline', $metadata))   $writer->setNewline($metadata['newline']);

        if ($type->description()->nativeType() === NativeType::COLLECTION) {
            foreach ($type as $item) {

                if ($writeHeader) {
                    if ($item->description()->nativeType() === NativeType::DICTIONARY) {
                        $writer->insertOne(array_keys($item->properties()));
                    }

                    $writeHeader = false;
                }


                    $writer->insertOne($this->convertToCsvRow($item));


            }
        } else {
            if ($writeHeader) {
                if ($type->description()->nativeType() === NativeType::DICTIONARY) {
                    $rows[] = array_keys($type->properties());
                }
            }

            $writer->insertOne($this->convertToCsvRow($type));
        }
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
        $itemProperties = $itemPrototype->typeProperties();

        foreach ($csvRow as $propertyName => $propertyValue) {

            if (! isset($itemProperties[$propertyName])) throw new \InvalidArgumentException(sprintf('Csv row property %s is not a known item property. Valid properties are %s', $propertyName, implode(', ', array_keys($itemProperties))));

            $propertyType = $itemProperties[$propertyName]->typePrototype()->of();

            $csvRow[$propertyName] = $propertyType::fromString((string)$csvRow[$propertyName]);
        }

        $itemType = $itemPrototype->of();

        return $itemType::fromNativeValue($csvRow);
    }

    /**
     * @param Type $type
     * @return array
     */
    protected function convertToCsvRow(Type $type)
    {
        switch ($type->description()->nativeType()) {
            case NativeType::COLLECTION:
            case NativeType::DICTIONARY:
                return array_map(function (Type $item) { return $item->toString(); }, $type->value());
                break;
            default:
                return [$type->toString()];
        }
    }
}
 
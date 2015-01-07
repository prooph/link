<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/8/14 - 5:57 PM
 */
namespace Application\Service;

use Ginger\Type\Description\Description;
use Ginger\Type\PrototypeProperty;
use SystemConfig\Projection\GingerConfig;
use SystemConfig\Service\NeedsSystemConfig;

/**
 * Class AbstractQueryController
 *
 * A query controller only communicates with the read side of the application and performs NO ACTIONS only queries.
 *
 * @package Application\Service
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
class AbstractQueryController extends \Zend\Mvc\Controller\AbstractActionController implements NeedsSystemConfig
{
    /**
     * @var GingerConfig
     */
    protected $systemConfig;

    /**
     * @param GingerConfig $systemConfig
     * @return void
     */
    public function setSystemConfig(GingerConfig $systemConfig)
    {
        $this->systemConfig = $systemConfig;
    }

    /**
     * Loads available DataTypes from system config and converts some to cient format
     *
     * If optional data type array is passed as argument, this is used instead of all available types
     *
     * @param array|null $dataTypes
     * @return array
     */
    protected function getDataTypesForClient(array $dataTypes = null)
    {
        if (is_null($dataTypes)) {
            $dataTypes = $this->systemConfig->getAllPossibleDataTypes();
        }

        return array_map(function($dataTypeClass) { return $this->prepareDataType($dataTypeClass); }, $dataTypes);
    }

    private function prepareDataType($dataTypeClass)
    {
        $properties = [];

        /** @var $typeProperty PrototypeProperty */
        foreach ($dataTypeClass::prototype()->typeProperties() as $typeProperty) {
            $properties[$typeProperty->propertyName()] = $this->prepareDataType($typeProperty->typePrototype()->of());
        }

        /** @var $description Description */
        $description = $dataTypeClass::prototype()->typeDescription();

        return [
            'value' => $dataTypeClass,
            'label' => $description->label(),
            'properties' => $properties,
            'native_type' => $description->nativeType()
        ];
    }
} 
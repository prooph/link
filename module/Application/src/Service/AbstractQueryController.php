<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/8/14 - 5:57 PM
 */
namespace Application\Service;

use Prooph\Processing\Type\Description\Description;
use Prooph\Processing\Type\PrototypeProperty;
use SystemConfig\Projection\ProcessingConfig;
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
     * @var ProcessingConfig
     */
    protected $systemConfig;

    /**
     * @param ProcessingConfig $systemConfig
     * @return void
     */
    public function setSystemConfig(ProcessingConfig $systemConfig)
    {
        $this->systemConfig = $systemConfig;
    }

    /**
     * Loads available DataTypes from system config and converts some to cient format
     *
     * If optional data type array is passed as argument, this is used instead of all available types
     *
     * @param array|null $processingTypes
     * @return array
     */
    protected function getProcessingTypesForClient(array $processingTypes = null)
    {
        if (is_null($processingTypes)) {
            $processingTypes = $this->systemConfig->getAllAvailableProcessingTypes();
        }

        return array_map(function($processingTypeClass) { return $this->prepareProcessingType($processingTypeClass); }, $processingTypes);
    }

    private function prepareProcessingType($processingTypeClass)
    {
        $properties = [];

        /** @var $typeProperty PrototypeProperty */
        foreach ($processingTypeClass::prototype()->typeProperties() as $typeProperty) {
            $properties[$typeProperty->propertyName()] = $this->prepareProcessingType($typeProperty->typePrototype()->of());
        }

        /** @var $description Description */
        $description = $processingTypeClass::prototype()->typeDescription();

        return [
            'value' => $processingTypeClass,
            'label' => $description->label(),
            'properties' => $properties,
            'native_type' => $description->nativeType()
        ];
    }
} 
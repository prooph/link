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
     * @param array|null $gingerTypes
     * @return array
     */
    protected function getGingerTypesForClient(array $gingerTypes = null)
    {
        if (is_null($gingerTypes)) {
            $gingerTypes = $this->systemConfig->getAllAvailableGingerTypes();
        }

        return array_map(function($gingerTypeClass) { return $this->prepareGingerType($gingerTypeClass); }, $gingerTypes);
    }

    private function prepareGingerType($gingerTypeClass)
    {
        $properties = [];

        /** @var $typeProperty PrototypeProperty */
        foreach ($gingerTypeClass::prototype()->typeProperties() as $typeProperty) {
            $properties[$typeProperty->propertyName()] = $this->prepareGingerType($typeProperty->typePrototype()->of());
        }

        /** @var $description Description */
        $description = $gingerTypeClass::prototype()->typeDescription();

        return [
            'value' => $gingerTypeClass,
            'label' => $description->label(),
            'properties' => $properties,
            'native_type' => $description->nativeType()
        ];
    }
} 
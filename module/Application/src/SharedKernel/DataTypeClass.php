<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/14/14 - 10:37 PM
 */
namespace Application\SharedKernel;

use Ginger\Message\MessageNameUtils;

/**
 * Class DataTypeClass
 *
 * Small helper to convert the data type part of a workflow message name back to the type class
 *
 * @package Application\SharedKernel
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class DataTypeClass 
{
    public static function extractFromMessageName($messageName, array $knownDataTypes)
    {
        if (! is_string($messageName)) throw new \InvalidArgumentException("Message name must be string");

        $dataType = MessageNameUtils::getTypePartOfMessageName($messageName);

        if (empty($dataType)) throw new \InvalidArgumentException(sprintf(
            "Invalid message name -%s- provided. Data type declaration could not be found.",
            $messageName
        ));

        foreach ($knownDataTypes as $knownDataType) {
            if (MessageNameUtils::normalize($knownDataType) === $dataType) return $knownDataType;
        }

        throw new \InvalidArgumentException(sprintf(
            "The data type %s can not be resolved to a known class: %s",
            $dataType,
            implode(", ", $knownDataTypes)
        ));
    }
} 
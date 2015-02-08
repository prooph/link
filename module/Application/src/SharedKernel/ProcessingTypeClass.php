<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/14/14 - 10:37 PM
 */
namespace Application\SharedKernel;

use Prooph\Processing\Message\MessageNameUtils;

/**
 * Class ProcessingTypeClass
 *
 * Small helper to convert the data type part of a workflow message name back to the type class
 *
 * @package Application\SharedKernel
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class ProcessingTypeClass
{
    public static function extractFromMessageName($messageName, array $knownProcessingTypes)
    {
        if (! is_string($messageName)) throw new \InvalidArgumentException("Message name must be string");

        $processingType = MessageNameUtils::getTypePartOfMessageName($messageName);

        if (empty($processingType)) throw new \InvalidArgumentException(sprintf(
            "Invalid message name -%s- provided. Data type declaration could not be found.",
            $messageName
        ));

        foreach ($knownProcessingTypes as $knownProcessingType) {
            if (MessageNameUtils::normalize($knownProcessingType) === $processingType) return $knownProcessingType;
        }

        throw new \InvalidArgumentException(sprintf(
            "The data type %s can not be resolved to a known class: %s",
            $processingType,
            implode(", ", $knownProcessingTypes)
        ));
    }
} 
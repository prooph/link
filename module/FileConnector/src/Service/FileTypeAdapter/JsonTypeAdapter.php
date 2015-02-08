<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 04.01.15 - 18:27
 */

namespace FileConnector\Service\FileTypeAdapter;

use FileConnector\Service\FileTypeAdapter;
use Prooph\Processing\Type\Prototype;
use Prooph\Processing\Type\Type;

/**
 * Class JsonTypeAdapter
 *
 * @package FileConnector\Service\FileTypeAdapter
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class JsonTypeAdapter implements FileTypeAdapter
{
    /**
     * @param string $filename
     * @param Prototype $prototype
     * @param array $metadata
     * @return mixed
     */
    public function readDataForType($filename, Prototype $prototype, array &$metadata = [])
    {
        $content = file_get_contents($filename);

        $data = json_decode($content, true);

        $typeClass = $prototype->of();

        $type = $typeClass::fromJsonDecodedData($data);

        return $type->value();
    }

    /**
     * @param string $filename
     * @param Type $type
     * @param array $metadata
     * @return void
     */
    public function writeDataOfType($filename, Type $type, array &$metadata = [])
    {
        file_put_contents($filename, json_encode($type));
    }
}
 
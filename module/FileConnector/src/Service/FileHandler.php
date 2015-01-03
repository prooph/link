<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 19:32
 */

namespace FileConnector\Service;

use Ginger\Type\Prototype;
use Ginger\Type\Type;

/**
 * Interface FileHandler
 *
 * @package FileConnector\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface FileHandler 
{
    /**
     * @param string $filename
     * @param Prototype $prototype
     * @return mixed
     * @throws \Exception
     */
    public function readDataForType($filename, Prototype $prototype);

    /**
     * @param string $filename
     * @param Type $type
     * @return void
     * @throws \Exception
     */
    public function writeDataOfType($filename, Type $type);
}
 
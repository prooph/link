<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 22:03
 */

namespace FileConnectorTest;

/**
 * Class TestCase
 *
 * @package FileConnectorTest
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @return string
     */
    protected function getTestDataPath()
    {
        return __DIR__ . '/data/';
    }
}
 
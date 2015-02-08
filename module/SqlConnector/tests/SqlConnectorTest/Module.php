<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.01.15 - 00:30
 */

namespace SqlConnectorTest;

final class Module 
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/processing.config.local.php';
    }
}
 
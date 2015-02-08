<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/26/15 - 7:47 PM
 */
namespace Application\SharedKernel;

/**
 * Class DataLocation
 *
 * This class represents the data directory where modules can manage their own data files.
 * The directory and all sub dirs should be writable.
 *
 * @package Application\SharedKernel
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class DataLocation extends AbstractLocation
{
    protected function additionalAssertPath($path)
    {
        if (!is_writable($path)) throw new \InvalidArgumentException(sprintf('Data dir %s should be be writable', $path));

        $subdirs = scandir($path);

        foreach ($subdirs as $dir) {
            if (strpos($dir, ".") === 0) continue;
            if (! is_dir($dir)) continue;

            $this->additionalAssertPath($dir);
        }
    }
}
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 21:59
 */

namespace SystemConfig\Model;

/**
 * Interface ConfigWriter
 *
 * @package SystemConfig\Model
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface ConfigWriter
{
    /**
     * @param array $config
     * @param string $path
     * @return void
     */
    public function writeNewConfigToDirectory(array $config, $path);
}
 
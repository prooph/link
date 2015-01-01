<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 22:48
 */

namespace SystemConfig;

/**
 * Class Definition
 *
 * @package SystemConfig
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class Definition
{
    /**
     * @return string
     */
    public static function getSystemConfigDir()
    {
        return getcwd() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'autoload';
    }

    /**
     * @return string
     */
    public static function getScriptsDir()
    {
        return getcwd() . DIRECTORY_SEPARATOR . 'scripts';
    }
}
 
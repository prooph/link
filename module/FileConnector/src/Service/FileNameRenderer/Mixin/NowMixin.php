<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 04.01.15 - 20:32
 */

namespace FileConnector\Service\FileNameRenderer\Mixin;

/**
 * Class NowMixin
 *
 * The NowMixin is looking for date format string and returns the current datetime in the specified format
 *
 * @example {{#now}}Y-m-d{{/now}}
 *
 * @package FileConnector\Service\FileNameRenderer\Mixin
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class NowMixin 
{
    public function __invoke()
    {
        return function ($format, $renderer) {
            if (false !== strstr($format, '{{')) $format = $renderer($format);
            if (empty($format))                  $format = 'Y-m-d';

            return date($format);
        };
    }
}
 
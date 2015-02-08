<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/14/14 - 5:21 PM
 */
namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;

/**
 * Class EmberPushToStore
 *
 * This ViewHelper takes a resource name and an array containing resources data to render a string
 * that can be used to push data into an ember data store to preload data server side.
 *
 * @example:
 * <code>
 * <?php
 * echo $this->emberPushToStore('post', [["id" => 1, "title" => "Using EmberPushToStore view helper"]]);
 * </code>
 *
 * //Output:
 * this.store.push('post', {
 *   id: 1,
 *   title: "Using EmberPushToStore view helper"
 * });
 *
 * //Many resources result in several push statements
 *
 * @package Application\View\Helper
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class EmberPushToStore extends AbstractHelper
{
    public function __invoke($resourceName, array $resources)
    {
        $output = "";
        foreach ($resources as $resourceData) {
            $output.= "this.store.push('" . $resourceName . "', this.store.normalize('" . $resourceName . "', " . json_encode($resourceData) . "));\n";
        }

        return $output;
    }
} 
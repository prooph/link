<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 04.01.15 - 20:17
 */

namespace FileConnector\Service;
use Phly\Mustache\Mustache;
use Zend\Stdlib\ArrayUtils;

/**
 * Class FileNameRenderer
 *
 * @package FileConnector\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FileNameRenderer 
{
    /**
     * @var Mustache
     */
    private $mustacheEngine;

    /**
     * @var callable[]
     */
    private $mixins;

    /**
     * @param Mustache $mustache
     * @param array $mixins
     */
    public function __construct(Mustache $mustache, array $mixins)
    {
        $this->mustacheEngine = $mustache;
        $this->mixins = $mixins;
    }

    /**
     * @param $filename_template
     * @param array $data
     * @return string
     */
    public function render($filename_template, array $data = [])
    {
        if (false === strstr($filename_template, '{{')) return $filename_template;

        $data = ArrayUtils::merge($data, $this->mixins);

        return $this->mustacheEngine->render($filename_template, $data);
    }
}
 
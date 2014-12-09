<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 22:16
 */

namespace SystemConfig\Service\ConfigWriter;

use SystemConfig\Model\ConfigWriter;
use Zend\Config\Writer\PhpArray;

/**
 * Class ZendPhpArrayWriter
 *
 * @package SystemConfig\Service\ConfigWriter
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ZendPhpArrayWriter implements ConfigWriter
{
    /**
     * @var PhpArray
     */
    private $zendWriter;

    /**
     * @param array $config
     * @param string $path
     * @return void
     */
    public function writeNewConfigToDirectory(array $config, $path)
    {
        $this->getWriter()->toFile($path, $config);
    }

    /**
     * @return PhpArray
     */
    private function getWriter()
    {
        if (is_null($this->zendWriter)) {
            $this->zendWriter = new PhpArray();
            $this->zendWriter->setUseBracketArraySyntax(true);
        }

        return $this->zendWriter;
    }
}
 
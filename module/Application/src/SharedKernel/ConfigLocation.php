<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.12.14 - 21:14
 */

namespace Application\SharedKernel;

/**
 * Class ConfigLocation
 *
 * This class represents the directory where the processing config file is located.
 *
 * @package Application\SharedKernel
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ConfigLocation extends AbstractLocation
{
    /**
     * @param $fileName
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getConfigArray($fileName)
    {
        $configFile = $this->path . DIRECTORY_SEPARATOR . $fileName;

        if (! file_exists($configFile)) throw new \InvalidArgumentException(sprintf('Config file %s does not exist', $configFile));
        if (! preg_match('/\.php$/', $fileName)) throw new \InvalidArgumentException(sprintf('Config file %s must be a php file', $configFile));

        $config = include $configFile;

        if (!is_array($config)) throw new \InvalidArgumentException(sprintf('Config read from %s is not an array', $configFile));

        return $config;
    }


    protected function additionalAssertPath($path)
    {
        if (!is_writable($path)) throw new \InvalidArgumentException(sprintf('Config location %s must be writable', $path));
    }
}
 
<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/27/15 - 8:08 PM
 */
namespace Application\SharedKernel;

/**
 * Class ApplicationDataTypeLocation
 *
 * This class describes the location for application wide data types.
 * These types are synchronized with all ginger nodes so that the
 * types are available on every node. Connectors should put their
 * types under the namespace Application\DataType\<ConnectorModule>\*
 * and write them to the appropriate directory mapping the namespace and
 * starting in the directory described by this location.
 *
 * @package Application\SharedKernel
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class ApplicationDataTypeLocation extends AbstractLocation
{
    /**
     * Writes the given class content to a class file named after the class.
     * The root directory is defined by the path of ApplicationDataTypeLocation.
     * The namespace of the class should start with Application\DataType\
     * If more sub namespaces are defined, the method creates a directory for each
     * namespace part if not already exists.
     *
     * @param $dataTypeFQCN
     * @param $classContent
     * @throws \InvalidArgumentException
     */
    public function addDataTypeClassIfNotExists($dataTypeFQCN, $classContent)
    {
        if (strpos($dataTypeFQCN, "Application\\DataType\\") !== 0) {
            throw new \InvalidArgumentException("Namespace of data type should start with Application\\DataType\\. Got " . $dataTypeFQCN);
        }
        $nsDirs = explode("\\", str_replace("Application\\DataType\\", "", $dataTypeFQCN));

        $className = array_pop($nsDirs);

        if (empty($className)) {
            throw new \InvalidArgumentException("Provided data type FQCN contains no class name: " . $dataTypeFQCN);
        }

        $currentPath = $this->toString() . DIRECTORY_SEPARATOR;

        if (! empty($nsDirs)) {
            foreach ($nsDirs as $nsDir) {
                if (! is_dir($nsDir)) mkdir($nsDir);
                $currentPath .= DIRECTORY_SEPARATOR . $nsDir;
            }
        }

        $filename = $currentPath . DIRECTORY_SEPARATOR . $className . ".php";

        if (file_exists($filename)) return;

        file_put_contents($filename, $classContent);
    }

    protected function additionalAssertPath($path)
    {
        if (!is_writable($path)) throw new \InvalidArgumentException(sprintf('Application data type location %s must be writable', $path));
    }
}
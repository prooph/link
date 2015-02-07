<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 04.01.15 - 16:59
 */

namespace FileConnectorTest\Service\FileHandler;
use FileConnectorTest\Bootstrap;
use FileConnectorTest\TestCase;

/**
 * Class FileTypeAdapterManagerFactoryTest
 *
 * @package FileConnectorTest\Service\FileHandler
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FileTypeAdapterManagerFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_a_file_type_adapter_manager_from_application_config_when_requesting_it_from_service_locator()
    {
        $fileTypeAdapters = Bootstrap::getServiceManager()->get('fileconnector.file_type_adapter_manager');

        $this->assertInstanceOf('FileConnector\Service\FileTypeAdapter\FileTypeAdapterManager', $fileTypeAdapters);
    }
}
 
<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 04.01.15 - 20:57
 */

namespace FileConnectorTest\Service\FileNameRenderer;
use FileConnectorTest\Bootstrap;
use FileConnectorTest\TestCase;

/**
 * Class MixinManagerFactoryTest
 *
 * @package FileConnectorTest\Service\FileNameRenderer
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class MixinManagerFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_a_mixin_manager_by_requesting_it_from_the_service_locator()
    {
        $mixinManager = Bootstrap::getServiceManager()->get('fileconnector.filename_mixin_manager');

        $this->assertInstanceOf('FileConnector\Service\FileNameRenderer\MixinManager', $mixinManager);
    }
}
 
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.01.15 - 00:23
 */

namespace SqlConnectorTest\Service\Factory;

use SqlConnectorTest\Bootstrap;
use SqlConnectorTest\TestCase;

/**
 * Class AbstractDoctrineTableGatewayFactoryTest
 *
 * @package SqlConnectorTest\Service\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class AbstractDoctrineTableGatewayFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_a_doctrine_table_gateway()
    {
        $tableGateway = Bootstrap::getServiceManager()->get('sqlconnector:::ginger_test_users');

        $this->assertInstanceOf('SqlConnector\Service\DoctrineTableGateway', $tableGateway);
    }
}
 
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 22:03
 */

namespace FileConnectorTest\Service\FileHandler;

use FileConnector\Service\FileHandler\LeagueCsvHandler;
use FileConnectorTest\DataType\TestUser;
use FileConnectorTest\DataType\TestUserCollection;
use FileConnectorTest\TestCase;

/**
 * Class LeagueCsvHandlerTest
 *
 * @package FileConnectorTest\Service\FileHandler
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class LeagueCsvHandlerTest extends TestCase
{
    /**
     * @var LeagueCsvHandler
     */
    private $fileHandler;

    protected function setUp()
    {
        $this->fileHandler = new LeagueCsvHandler();
    }

    /**
     * @test
     */
    public function it_reads_users_from_csv_that_contains_a_header_row()
    {
        $usersData = $this->fileHandler->readDataForType($this->getTestDataPath() . "users-header.csv", TestUserCollection::prototype());

        $testUsers = TestUserCollection::fromNativeValue($usersData);

        $this->assertEquals(3, count($testUsers));
        $this->assertEquals(
            ['John Doe', 'Max Mustermann', 'Donald Duck'],
            array_map(
                function (TestUser $user) { return $user->property('name')->value(); },
                $testUsers->value()
            )
        );
    }

    /**
     * @test
     */
    public function it_reads_users_from_csv_that_has_no_header_row_but_where_columns_match_user_properties()
    {
        $usersData = $this->fileHandler->readDataForType($this->getTestDataPath() . "users-no-header.csv", TestUserCollection::prototype());

        $testUsers = TestUserCollection::fromNativeValue($usersData);

        $this->assertEquals(3, count($testUsers));
        $this->assertEquals(
            ['John Doe', 'Max Mustermann', 'Donald Duck'],
            array_map(
                function (TestUser $user) { return $user->property('name')->value(); },
                $testUsers->value()
            )
        );
    }
}
 
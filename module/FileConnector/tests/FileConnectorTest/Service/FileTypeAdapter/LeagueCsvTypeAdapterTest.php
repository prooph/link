<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 22:03
 */

namespace FileConnectorTest\Service\FileHandler;

use FileConnector\Service\FileTypeAdapter\LeagueCsvTypeAdapter;
use FileConnectorTest\DataType\TestUser;
use FileConnectorTest\DataType\TestUserCollection;
use FileConnectorTest\TestCase;

/**
 * Class LeagueCsvTypeAdapterTest
 *
 * @package FileConnectorTest\Service\FileTypeAdapter
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class LeagueCsvTypeAdapterTest extends TestCase
{
    /**
     * @var LeagueCsvTypeAdapter
     */
    private $fileTypeAdapter;

    private $tempFile;

    protected function setUp()
    {
        $this->fileTypeAdapter = new LeagueCsvTypeAdapter();
        $this->tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'users-written.csv';
    }

    protected function tearDown()
    {
        if (file_exists($this->tempFile)) unlink($this->tempFile);
    }

    /**
     * @test
     */
    public function it_reads_users_from_csv_that_contains_a_header_row()
    {
        $usersData = $this->fileTypeAdapter->readDataForType($this->getTestDataPath() . "users-header.csv", TestUserCollection::prototype());

        $testUsers = TestUserCollection::fromNativeValue($usersData);

        $this->assertEquals(3, count($testUsers->value()));
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
        $usersData = $this->fileTypeAdapter->readDataForType($this->getTestDataPath() . "users-no-header.csv", TestUserCollection::prototype());

        $testUsers = TestUserCollection::fromNativeValue($usersData);

        $this->assertEquals(3, count($testUsers->value()));
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
    public function it_passes_metadata_to_csv_reader_to_handle_other_delimiter_in_csv_file()
    {
        $metadata = ['delimiter' => ';'];

        $usersData = $this->fileTypeAdapter->readDataForType(
            $this->getTestDataPath() . "users-other-delimiter.csv",
            TestUserCollection::prototype(),
            $metadata
        );

        $testUsers = TestUserCollection::fromNativeValue($usersData);

        $this->assertEquals(3, count($testUsers->value()));
        $this->assertEquals(3, $metadata['total_items']);
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
    public function it_only_collects_specified_range_but_returns_total_item_count()
    {
        $metadata = ['offset' => 1, 'limit' => 2];

        $usersData = $this->fileTypeAdapter->readDataForType(
            $this->getTestDataPath() . "users-header.csv",
            TestUserCollection::prototype(),
            $metadata
        );

        $testUsers = TestUserCollection::fromNativeValue($usersData);

        $this->assertEquals(2, count($testUsers->value()));
        $this->assertEquals(3, $metadata['total_items']);
        $this->assertEquals(
            ['Max Mustermann', 'Donald Duck'],
            array_map(
                function (TestUser $user) { return $user->property('name')->value(); },
                $testUsers->value()
            )
        );
    }

    /**
     * @test
     * @dataProvider provideTestUsers
     */
    public function it_writes_users_to_csv_file(TestUserCollection $users)
    {
        if (! is_writable(sys_get_temp_dir())) $this->markTestSkipped(sprintf("Temp dir %s is not writable", sys_get_temp_dir()));

        $this->fileTypeAdapter->writeDataOfType($this->tempFile, $users);

        $readUsersData = $this->fileTypeAdapter->readDataForType($this->tempFile, $users->prototype());

        $readUsers = TestUserCollection::fromNativeValue($readUsersData);

        $this->assertEquals(2, count($readUsers->value()));
        $this->assertEquals(
            ['John Doe', 'Max Mustermann'],
            array_map(
                function (TestUser $user) { return $user->property('name')->value(); },
                $readUsers->value()
            )
        );
    }

    /**
     * @test
     * @dataProvider provideTestUsers
     */
    public function it_writes_users_to_csv_file_using_custom_delimiter(TestUserCollection $users)
    {
        if (! is_writable(sys_get_temp_dir())) $this->markTestSkipped(sprintf("Temp dir %s is not writable", sys_get_temp_dir()));

        $metadata = ['delimiter' => ";"];

        $this->fileTypeAdapter->writeDataOfType($this->tempFile, $users, $metadata);

        $readUsersData = $this->fileTypeAdapter->readDataForType($this->tempFile, $users->prototype(), $metadata);

        $readUsers = TestUserCollection::fromNativeValue($readUsersData);

        $this->assertEquals(2, count($readUsers->value()));
        $this->assertEquals(
            ['John Doe', 'Max Mustermann'],
            array_map(
                function (TestUser $user) { return $user->property('name')->value(); },
                $readUsers->value()
            )
        );
    }

    /**
     * @test
     * @dataProvider provideTestUsers
     */
    public function it_adds_a_user_to_existing_file(TestUserCollection $users)
    {
        if (! is_writable(sys_get_temp_dir())) $this->markTestSkipped(sprintf("Temp dir %s is not writable", sys_get_temp_dir()));

        $this->fileTypeAdapter->writeDataOfType($this->tempFile, $users);

        $donaldDuck = TestUser::fromNativeValue([
            'id' => 3,
            'name' => 'Donald Duck',
            'age' => 57
        ]);

        $this->fileTypeAdapter->writeDataOfType($this->tempFile, $donaldDuck);

        $readUsersData = $this->fileTypeAdapter->readDataForType($this->tempFile, $users->prototype());

        $readUsers = TestUserCollection::fromNativeValue($readUsersData);

        $this->assertEquals(3, count($readUsers->value()));
        $this->assertEquals(
            ['John Doe', 'Max Mustermann', 'Donald Duck'],
            array_map(
                function (TestUser $user) { return $user->property('name')->value(); },
                $readUsers->value()
            )
        );
    }

    /**
     * @return array
     */
    public function provideTestUsers()
    {
        $users = TestUserCollection::fromNativeValue([
            [
                'id' => 1,
                'name' => 'John Doe',
                'age' => 34
            ],
            [
                'id' => 2,
                'name' => 'Max Mustermann',
                'age' => 41
            ],
        ]);

        return [
            [
                $users
            ]
        ];
    }
}
 
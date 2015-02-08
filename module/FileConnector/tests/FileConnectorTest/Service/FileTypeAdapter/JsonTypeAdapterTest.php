<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 04.01.15 - 18:32
 */

namespace FileConnectorTest\Service\FileHandler;

use FileConnector\Service\FileTypeAdapter\JsonTypeAdapter;
use FileConnectorTest\DataType\TestUserCollection;
use FileConnectorTest\TestCase;
use FileConnectorTest\DataType\TestUser;

/**
 * Class JsonTypeAdapterTest
 *
 * @package FileConnectorTest\Service\FileHandler
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class JsonTypeAdapterTest extends TestCase
{
    /**
     * @var JsonTypeAdapter
     */
    private $fileTypeAdapter;

    private $tempFile;

    protected function setUp()
    {
        $this->fileTypeAdapter = new JsonTypeAdapter();

        $this->tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'users.json';
    }

    protected function tearDown()
    {
        if (file_exists($this->tempFile)) unlink($this->tempFile);
    }

    /**
     * @test
     */
    public function it_reads_data_for_user_from_json()
    {
        $data = $this->fileTypeAdapter->readDataForType($this->getTestDataPath() . 'testuser_john_doe.json', TestUser::prototype());

        $user = TestUser::fromNativeValue($data);

        $this->assertEquals('John Doe', $user->property('name')->value());
    }

    /**
     * @test
     */
    public function it_writes_users_to_json_file()
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

        $this->fileTypeAdapter->writeDataOfType($this->tempFile, $users);

        $readUsersData = $this->fileTypeAdapter->readDataForType($this->tempFile, TestUserCollection::prototype());

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
}
 
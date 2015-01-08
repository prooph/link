<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.01.15 - 16:46
 */

namespace FileConnector\Api;

use Application\Service\AbstractRestController;
use Application\Service\ActionController;
use FileConnector\FileManager\FileConnectorTranslator;
use Prooph\ServiceBus\CommandBus;
use SystemConfig\Command\AddConnectorToConfig;
use SystemConfig\Command\ChangeConnectorConfig;
use SystemConfig\Projection\GingerConfig;
use SystemConfig\Service\NeedsSystemConfig;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

/**
 * Class FileConnector
 *
 * @package FileConnector\Api
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FileConnector extends AbstractRestController implements ActionController, NeedsSystemConfig
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var GingerConfig
     */
    private $systemConfig;

    /**
     * @var array
     */
    private $availableFileTypes;

    /**
     * @param array $availableFileTypes
     */
    public function __construct(array $availableFileTypes)
    {
        $this->availableFileTypes = $availableFileTypes;
    }

    /**
     * @param array $data
     * @return mixed|void
     */
    public function create(array $data)
    {
        if (! array_key_exists("connector", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Root key connector missing in request data'));

        $data = $data["connector"];

        $result = $this->validateConnectorData($data);

        if ($result instanceof ApiProblemResponse) return $result;

        $data = FileConnectorTranslator::translateFromClient($data);

        $id = FileConnectorTranslator::generateConnectorId($data);

        $this->commandBus->dispatch(AddConnectorToConfig::fromDefinition(
            $id,
            $data['name'],
            $data['allowed_messages'],
            $data['allowed_types'],
            $this->systemConfig->getConfigLocation(),
            [
                'metadata' => $data['metadata'],
                'ui_metadata_key' => 'FileConnectorMetadata',
            ]
        ));

        $data = FileConnectorTranslator::translateToClient($data);

        $data['id'] = $id;

        return ["connector" => $data];
    }

    /**
     * @param mixed $id
     * @param array $data
     * @return array|mixed|null|ApiProblemResponse
     */
    public function update($id, array $data)
    {
        if (! $this->existsConnector($id)) return new ApiProblemResponse(new ApiProblem(404, 'Connector can not be found'));

        if (! array_key_exists("connector", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Root key connector missing in request data'));

        $data = $data["connector"];

        $data['ui_metadata_key'] = 'FileConnectorMetadata';

        $result = $this->validateConnectorData($data);

        if ($result instanceof ApiProblemResponse) return $result;

        $data = FileConnectorTranslator::translateFromClient($data);

        $this->commandBus->dispatch(ChangeConnectorConfig::ofConnector($id, $data, $this->systemConfig->getConfigLocation()));

        $data = FileConnectorTranslator::translateToClient($data);

        $data['id'] = $id;

        return ['connector' => $data];
    }

    /**
     * @param CommandBus $commandBus
     * @return void
     */
    public function setCommandBus(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param GingerConfig $systemConfig
     * @return void
     */
    public function setSystemConfig(GingerConfig $systemConfig)
    {
        $this->systemConfig = $systemConfig;
    }

    /**
     * @param $connectorId
     * @return bool
     */
    private function existsConnector($connectorId)
    {
        return isset($this->systemConfig->getConnectors()[$connectorId]);
    }

    /**
     * @param array $data
     * @return null|ApiProblemResponse
     */
    private function validateConnectorData(array $data)
    {
        if (! array_key_exists("name", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Connector name missing in request data'));
        if (! array_key_exists("data_type", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Data type missing in request data'));
        if (! array_key_exists("writable", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Writable missing in request data'));
        if (! array_key_exists("readable", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Readable missing in request data'));
        if (! array_key_exists("metadata", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Metadata missing in request data'));
        if (! is_array($data["metadata"])) return new ApiProblemResponse(new ApiProblem(422, 'Metadata must be an array'));
        if (! array_key_exists("file_type", $data["metadata"])) return new ApiProblemResponse(new ApiProblem(422, 'Metadata.file_type missing in request data'));

        if (! in_array($data['metadata']['file_type'], $this->availableFileTypes)) return new ApiProblemResponse(new ApiProblem(422, 'Metadata.file_type is not a known file type'));

        return null;
    }
}
 
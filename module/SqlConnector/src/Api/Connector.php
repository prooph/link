<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/26/15 - 7:31 PM
 */
namespace SqlConnector\Api;

use Application\Service\AbstractRestController;
use SqlConnector\Service\SqlConnectorTranslator;
use SqlConnector\Service\TableConnectorGenerator;

/**
 * Class Connector
 *
 * @package SqlConnector\Api
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class Connector extends AbstractRestController
{
    /**
     * @var TableConnectorGenerator
     */
    private $tableConnectorGenerator;

    public function __construct(TableConnectorGenerator $tableConnectorGenerator)
    {
        $this->tableConnectorGenerator = $tableConnectorGenerator;
    }

    public function create(array $data)
    {
        $connectorId = SqlConnectorTranslator::generateConnectorId();

        $this->tableConnectorGenerator->addConnector(
            $connectorId,
            $data
        );

        $data['id'] = $connectorId;

        return $data;
    }

    public function update($id, $data)
    {
        unset($data['id']);

        $regenerateTypes = isset($data['regenerate_type'])? (bool)$data['regenerate_type'] : false;

        unset($data['regenerate_type']);

        $this->tableConnectorGenerator->updateConnector($id, $data, $regenerateTypes);

        $data['id'] = $id;

        return $data;
    }
} 
<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 26.01.15 - 00:19
 */

namespace SqlConnector\Api;

use Application\Service\AbstractRestController;
use Doctrine\DBAL\DriverManager;
use SqlConnector\Service\DbalConnectionCollection;
use ZF\ContentNegotiation\JsonModel;

/**
 * Class Table
 *
 * @package SqlConnector\Api
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class Table extends AbstractRestController
{
    /**
     * @var DbalConnectionCollection
     */
    private $dbalConnections;

    /**
     * @param DbalConnectionCollection $connections
     */
    public function __construct(DbalConnectionCollection $connections)
    {
        $this->dbalConnections = $connections;
    }

    public function getList()
    {
        $connectionDb = $this->params('dbname');

        if (! $this->dbalConnections->containsKey($connectionDb)) {
            return $this->getApiProblemResponse(404, 'Dbal connection can not be found');
        }

        $connection = $this->dbalConnections->get($connectionDb);

        $tables = $connection->connection()->getSchemaManager()->listTableNames();

        return new JsonModel([ 'payload' => array_map(function ($tablename) {
            return ["name" => $tablename];
        }, $tables)]);
    }
}
 
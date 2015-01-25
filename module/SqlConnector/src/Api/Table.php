<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 26.01.15 - 00:19
 */

namespace SqlConnector\Api;

use Application\Service\AbstractRestController;
use Doctrine\DBAL\DriverManager;

/**
 * Class Table
 *
 * @package SqlConnector\Api
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class Table extends AbstractRestController
{
    /**
     * @var \ArrayObject
     */
    private $dbalConnections;

    /**
     * @param \ArrayObject $connections
     */
    public function __construct(\ArrayObject $connections)
    {
        $this->dbalConnections = $connections;
    }

    public function getList()
    {
        $connectionDb = $this->params('dbname');

        if (! isset($this->dbalConnections[$connectionDb])) {
            return $this->getApiProblemResponse(404, 'Dbal connection can not be found');
        }

        $connectionConfig = $this->dbalConnections[$connectionDb];

        $con = DriverManager::getConnection($connectionConfig);

        $tables = $con->getSchemaManager()->listTableNames();

        return array_map(function ($tablename) {
            return ["name" => $tablename];
        }, $tables);
    }
}
 
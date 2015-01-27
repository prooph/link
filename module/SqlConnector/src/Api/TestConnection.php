<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 25.01.15 - 20:20
 */

namespace SqlConnector\Api;

use Application\Service\AbstractRestController;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Zend\Http\PhpEnvironment\Response;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

/**
 * Class TestConnection
 *
 * @package SqlConnector\Api
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class TestConnection extends AbstractRestController
{
    public function create($data)
    {
        try {
            //Test if we are able to list available tables
            $con = DriverManager::getConnection($data);

            $tables = $con->getSchemaManager()->listTables();
        } catch (\Exception $e) {
            return new ApiProblemResponse(new ApiProblem(422, "Connection failed: " . $e->getMessage()));
        }

        /** @var $response Response */
        $response = $this->getResponse();

        $response->setStatusCode(201);

        return $response;
    }
}
 
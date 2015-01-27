<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 24.01.15 - 17:20
 */

namespace SqlConnector\Controller;

use Application\Service\AbstractQueryController;
use SqlConnector\Service\DbalConnectionCollection;
use SqlConnector\Service\SqlConnectorTranslator;
use Zend\View\Model\ViewModel;

final class SqlManagerController extends AbstractQueryController
{
    /**
     * @var DbalConnectionCollection
     */
    private $dbalConnections;

    /**
     * @param DbalConnectionCollection $dbalConnections
     */
    public function __construct(DbalConnectionCollection $dbalConnections)
    {
        $this->dbalConnections = $dbalConnections;
    }

    public function startAppAction()
    {
        $view = new ViewModel([
            'sql_connectors' => $this->getSqlConnectorsForClient(),
            'dbal_connections' => array_values($this->dbalConnections->toArray()),
        ]);

        $view->setTemplate('sqlconnector/sql-manager/app');

        $this->layout()->setVariable('includeRiotJs', true);

        return $view;
    }

    private function getSqlConnectorsForClient()
    {
        $sqlConnectors = [];

        foreach ($this->systemConfig->getConnectors() as $id => $connector) {
            if (strpos($id, "sqlconnector:::") !== false) {
                $connector['id'] = $id;
                $sqlConnectors[] = SqlConnectorTranslator::translateToClient($connector);
            }
        }

        return $sqlConnectors;
    }
}
 
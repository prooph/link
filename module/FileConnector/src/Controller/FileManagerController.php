<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.01.15 - 13:38
 */

namespace FileConnector\Controller;

use Application\Service\AbstractQueryController;
use SystemConfig\Projection\GingerConfig;
use SystemConfig\Service\NeedsSystemConfig;
use Zend\View\Model\ViewModel;

/**
 * Class FileManagerController
 *
 * @package FileConnector\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FileManagerController extends AbstractQueryController
{
    /**
     * @var array
     */
    private $availableFileTypes = array();

    /**
     * @param array $availableFileTypes
     */
    public function __construct(array $availableFileTypes)
    {
        $this->availableFileTypes = $availableFileTypes;
    }

    public function startAppAction()
    {
        $viewModel = new ViewModel([
            'file_connectors' => $this->getFileConnectorsForClient(),
            'system_connectors' => $this->systemConfig->getConnectors(),
            'available_data_types' => $this->getDataTypesForClient(),

        ]);

        $viewModel->setTemplate('file-connector/file-manager/app');

        return $viewModel;
    }

    private function getFileConnectorsForClient()
    {
        return array_map(
            'FileConnector\FileManager\FileConnectorTranslator::translateForClient',
            array_filter(
                $this->systemConfig->getConnectors(),
                function ($connector, $id) {
                    return strpos($id, "fileconnector:::") !== false;
                }
            )

        );
    }
}
 
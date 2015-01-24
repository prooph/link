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
            'available_ginger_types' => $this->getGingerTypesForClient(),
            'available_file_types' => $this->availableFileTypes,

        ]);

        $viewModel->setTemplate('file-connector/file-manager/app');

        $this->layout()->setVariable('includeEmberJs', true);

        return $viewModel;
    }

    private function getFileConnectorsForClient()
    {
        $fileConnectors = [];

        foreach ($this->systemConfig->getConnectors() as $id => $connector) {
            if (strpos($id, "filegateway:::") !== false) {
                $connector['id'] = $id;
                $fileConnectors[$id] = $connector;
            }
        }

        return array_map(
            'FileConnector\FileManager\FileGatewayTranslator::translateToClient',
            $fileConnectors
        );
    }
}
 
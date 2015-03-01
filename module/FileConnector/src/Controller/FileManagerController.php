<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.01.15 - 13:38
 */

namespace FileConnector\Controller;

use Application\Service\AbstractQueryController;
use SystemConfig\Projection\ProcessingConfig;
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
            'available_processing_types' => $this->getProcessingTypesForClient(),
            'available_file_types' => $this->availableFileTypes,

        ]);

        $viewModel->setTemplate('file-connector/file-manager/app');

        $this->layout()->setVariable('includeRiotJs', true);

        return $viewModel;
    }

    private function getFileConnectorsForClient()
    {
        $fileConnectors = [];

        foreach ($this->systemConfig->getConnectors() as $id => $connector) {
            if (strpos($id, "filegateway:::") !== false) {
                $connector['id'] = $id;
                $fileConnectors[] = $connector;
            }
        }

        return array_map(
            'FileConnector\FileManager\FileGatewayTranslator::translateToClient',
            $fileConnectors
        );
    }
}
 
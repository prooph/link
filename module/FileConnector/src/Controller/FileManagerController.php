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
final class FileManagerController extends AbstractQueryController implements NeedsSystemConfig
{
    /**
     * @var GingerConfig
     */
    private $systemConfig;

    public function startAppAction()
    {
        $viewModel = new ViewModel([
            'connectors' => [],
        ]);

        $viewModel->setTemplate('file-connector/file-manager/app');

        return $viewModel;
    }

    /**
     * @param GingerConfig $systemConfig
     * @return void
     */
    public function setSystemConfig(GingerConfig $systemConfig)
    {
        $this->systemConfig = $systemConfig;
    }
}
 
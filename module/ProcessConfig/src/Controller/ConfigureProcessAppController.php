<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.12.14 - 19:35
 */

namespace ProcessConfig\Controller;

use Application\Service\AbstractQueryController;
use Application\SharedKernel\ConfigLocation;
use SystemConfig\Definition;
use SystemConfig\Model\GingerConfig;
use ZF\ContentNegotiation\ViewModel;

/**
 * Class ConfigureProcessAppController
 *
 * @package ProcessConfig\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ConfigureProcessAppController extends AbstractQueryController
{
    public function startAppAction()
    {
        $gingerConfig = GingerConfig::asProjectionFrom(ConfigLocation::fromPath(Definition::SYSTEM_CONFIG_DIR));

        $viewModel = new ViewModel([
            'process' => null,
            'possible_ginger_types' => $gingerConfig->getAllPossibleGingerTypes()
        ]);

        $viewModel->setTemplate('process-config/configure-process-app/app');

        return $viewModel;
    }
}
 
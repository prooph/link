<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 20:21
 */

namespace SystemConfig\Controller;

use Application\Service\AbstractQueryController;
use SystemConfig\Projection\GingerConfig;
use SystemConfig\Service\NeedsSystemConfig;
use Zend\View\Model\ViewModel;

/**
 * Class OverviewController
 *
 * @package SystemConfig\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class OverviewController extends AbstractQueryController implements NeedsSystemConfig
{
    /**
     * @var GingerConfig
     */
    private $systemConfig;
    /**
     * @return ViewModel
     */
    public function showAction()
    {
        $params = [];
        try {
            $params['gingerConfig'] = $this->systemConfig;
            $params['error'] = false;
        } catch (\Exception $ex) {
            $params['gingerConfig'] = null;
            $params['error'] = $ex->getMessage();
        }

        $params['config_dir_is_writable'] = is_writable($this->systemConfig->getConfigLocation()->toString());
        $params['config_is_writable'] = is_writable($this->systemConfig->getConfigLocation()->toString() . DIRECTORY_SEPARATOR . \SystemConfig\Model\GingerConfig::configFileName());
        $params['config_dir'] = $this->systemConfig->getConfigLocation()->toString();
        $params['config_file_name'] = \SystemConfig\Model\GingerConfig::configFileName();

        return new ViewModel($params);
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
 
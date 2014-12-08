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
use SystemConfig\Definition;
use SystemConfig\Projection\GingerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;

/**
 * Class OverviewController
 *
 * @package SystemConfig\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class OverviewController extends AbstractQueryController
{
    /**
     * @var GingerConfig
     */
    private $gingerConfig;

    /**
     * @param GingerConfig $gingerConfig
     */
    public function __construct(GingerConfig $gingerConfig)
    {
        $this->gingerConfig = $gingerConfig;
        new ServiceManager();
    }

    /**
     * @return ViewModel
     */
    public function showAction()
    {
        return new ViewModel([
            'gingerConfig' => $this->gingerConfig,
            'config_dir_is_writable' => is_writable(Definition::SYSTEM_CONFIG_DIR),
            'config_dir' => Definition::SYSTEM_CONFIG_DIR
        ]);
    }
}
 
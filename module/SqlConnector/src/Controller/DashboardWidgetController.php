<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 02.01.15 - 18:11
 */

namespace SqlConnector\src\Controller;

use Dashboard\Controller\AbstractWidgetController;
use Dashboard\View\DashboardWidget;
use SystemConfig\Projection\GingerConfig;
use SystemConfig\Service\NeedsSystemConfig;

/**
 * Class DashboardWidgetController
 *
 * @package SqlConnector\src\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class DashboardWidgetController extends AbstractWidgetController implements NeedsSystemConfig
{
    /**
     * @return DashboardWidget
     */
    public function widgetAction()
    {

    }

    /**
     * @param GingerConfig $systemConfig
     * @return void
     */
    public function setSystemConfig(GingerConfig $systemConfig)
    {
        // TODO: Implement setSystemConfig() method.
    }
}
 
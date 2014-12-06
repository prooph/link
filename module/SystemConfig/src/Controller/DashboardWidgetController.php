<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 06.12.14 - 22:32
 */

namespace SystemConfig\Controller;

use Dashboard\Controller\AbstractWidgetController;
use Dashboard\View\DashboardWidget;
use SystemConfig\Projection\GingerConfig;

/**
 * Class DashboardWidgetController
 *
 * @package SystemConfig\src\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DashboardWidgetController extends AbstractWidgetController
{
    /**
     * @var GingerConfig
     */
    private $gingerConfig;

    public function __construct(GingerConfig $gingerConfig)
    {
        $this->gingerConfig = $gingerConfig;
    }

    /**
     * @return DashboardWidget
     */
    public function widgetAction()
    {
        $variables = [
            'gingerIsConfigured' => $this->gingerConfig->isConfigured()
        ];

        return DashboardWidget::initialize('system-config/dashboard/widget', 'System Configuration', 4, $variables);
    }
}
 
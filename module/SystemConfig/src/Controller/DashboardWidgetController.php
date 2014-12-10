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

use Application\Service\TranslatorAwareController;
use Application\SharedKernel\ConfigLocation;
use Dashboard\Controller\AbstractWidgetController;
use Dashboard\View\DashboardWidget;
use SystemConfig\Definition;
use SystemConfig\Model\GingerConfig;
use Zend\Mvc\I18n\Translator;

/**
 * Class DashboardWidgetController
 *
 * @package SystemConfig\src\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DashboardWidgetController extends AbstractWidgetController
{
    /**
     * @return DashboardWidget
     */
    public function widgetAction()
    {
        $params = [];
        try {
            $params['gingerConfig'] = GingerConfig::asProjectionFrom(ConfigLocation::fromPath(Definition::SYSTEM_CONFIG_DIR));
            $params['error'] = false;
        } catch (\Exception $ex) {
            $params['gingerConfig'] = null;
            $params['error'] = $ex->getMessage();
        }

        return DashboardWidget::initialize('system-config/dashboard/widget', 'System Configuration', 4, $params);
    }
}
 
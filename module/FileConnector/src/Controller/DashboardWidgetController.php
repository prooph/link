<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.01.15 - 13:15
 */

namespace FileConnector\Controller;

use Dashboard\Controller\AbstractWidgetController;
use Dashboard\View\DashboardWidget;
use SystemConfig\Projection\GingerConfig;
use SystemConfig\Service\NeedsSystemConfig;

/**
 * Class DashboardWidgetController
 *
 * @package FileConnector\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class DashboardWidgetController extends AbstractWidgetController
{

    /**
     * @return DashboardWidget
     */
    public function widgetAction()
    {
        if (! $this->systemConfig->isConfigured()) return false;

        $connectors = [];

        foreach ($this->systemConfig->getConnectors() as $connectorId => $connector) {
            if (strpos($connectorId, 'filegateway:::') !== false) $connectors[$connectorId] = $connector;
        }

        return DashboardWidget::initialize(
            'file-connector/dashboard/widget',
            'File Connector',
            4,
            ['gingerConfig' => $this->systemConfig, 'fileConnectors' => $connectors]
        );
    }
}
 
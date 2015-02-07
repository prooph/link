<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 02.01.15 - 18:11
 */

namespace SqlConnector\Controller;

use Dashboard\Controller\AbstractWidgetController;
use Dashboard\View\DashboardWidget;
use SystemConfig\Projection\ProcessingConfig;
use SystemConfig\Service\NeedsSystemConfig;

/**
 * Class DashboardWidgetController
 *
 * @package SqlConnector\src\Controller
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
            if (strpos($connectorId, 'sqlconnector:::') !== false) $connectors[$connectorId] = $connector;
        }

        return DashboardWidget::initialize(
            'sqlconnector/dashboard/widget',
            'Sql Table Connector',
            4,
            ['processingConfig' => $this->systemConfig, 'sqlConnectors' => $connectors]
        );
    }
}
 
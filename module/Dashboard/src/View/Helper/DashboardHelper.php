<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 06.12.14 - 23:02
 */

namespace Dashboard\View\Helper;

use Dashboard\Service\DashboardProvider;
use Zend\View\Helper\AbstractHelper;

/**
 * Class DashboardHelper
 *
 * @package Dashboard\View\Helper
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DashboardHelper extends AbstractHelper
{
    /**
     * @var DashboardProvider
     */
    private $dashboardProvider;

    /**
     * @var string
     */
    private $widgetTemplate = '<div class="col-md-%d">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">%s</h3>
            </div>
            <div class="panel-body">%s</div>
        </div>
    </div>';

    /**
     * @param DashboardProvider $dashboardProvider
     */
    public function __construct(DashboardProvider $dashboardProvider)
    {
        $this->dashboardProvider = $dashboardProvider;
    }

    /**
     * Prints the dashboard
     */
    public function __invoke()
    {
        $currentCols = 0;

        $widgets = $this->dashboardProvider->provideWidgets();

        $html = '<div class="row">';

        foreach ($widgets as $widget) {
            if ($currentCols + $widget->getRequiredCols() > 12) {
                $html.= '</div><hr><div class="row">';
                $currentCols = 0;
            }

            $currentCols += $widget->getRequiredCols();

            $html.= sprintf($this->widgetTemplate, $widget->getRequiredCols(), $widget->getTitle(), $this->getView()->render($widget));
        }

        return $html . '</div>';

    }
}
 
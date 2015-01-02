<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 06.12.14 - 21:26
 */

namespace Dashboard\Controller;

use Application\Service\AbstractQueryController;
use Zend\View\Model\ViewModel;

/**
 * Class IndexController
 *
 * This controller is the application entry point. In the associated view the DashboardViewHelper requests and
 * renders all activated dashboard widgets.
 *
 *
 * @package Dashboard\Controller
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
class IndexController extends AbstractQueryController
{
    public function indexAction()
    {
        $viewModel = new ViewModel();

        $viewModel->setTemplate('application/index/index');

        return $viewModel;
    }
}

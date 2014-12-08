<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/8/14 - 5:53 PM
 */
namespace Application\Service;

use Prooph\ServiceBus\CommandBus;

/**
 * Class AbstractActionController
 *
 * Basic action controller that is is aware of using a command bus to send commands to the model.
 *
 * @package Application\Service
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
class AbstractActionController extends \Zend\Mvc\Controller\AbstractActionController implements ActionController
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @param CommandBus $commandBus
     * @return void
     */
    public function setCommandBus(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }
}
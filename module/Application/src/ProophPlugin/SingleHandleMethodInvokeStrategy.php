<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/8/14 - 10:55 PM
 */
namespace Application\ProophPlugin;

use Prooph\ServiceBus\InvokeStrategy\AbstractInvokeStrategy;

/**
 * Class SingleHandleMethodInvokeStrategy
 *
 * @package Application\ProophPlugin
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class SingleHandleMethodInvokeStrategy extends AbstractInvokeStrategy
{
    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     * @return bool
     */
    protected function canInvoke($aHandler, $aCommandOrEvent)
    {
        return method_exists($aHandler, 'handle');
    }

    /**
     * @param mixed $aHandler
     * @param mixed $aCommandOrEvent
     */
    protected function invoke($aHandler, $aCommandOrEvent)
    {
        $aHandler->handle($aCommandOrEvent);
    }
}
<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/27/15 - 9:14 PM
 */
namespace SqlConnectorTest\Mock;

use Prooph\ServiceBus\CommandBus;

final class CommandBusMock extends CommandBus
{
    private $lastMessage;

    public function dispatch($message)
    {
        $this->lastMessage = $message;
    }

    public function getLastMessage()
    {
        return $this->lastMessage;
    }

    public function reset()
    {
        $this->lastMessage = null;
    }
} 
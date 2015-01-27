<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.01.15 - 01:40
 */

namespace ProcessorProxy\ProophPlugin;

use ProcessorProxy\Command\ForwardHttpMessage;
use Prooph\ServiceBus\Message\MessageInterface;
use Prooph\ServiceBus\Message\ToMessageTranslatorInterface;

/**
 * Class ServiceBusMessageExtractor
 *
 * This class is a special Prooph\ServiceBus\Message\ToMessageTranslator.
 * It pulls the service bus message out of a ProcessorProxy\ForwardHttpMessage command.
 *
 * @package ProcessorProxy\ProophPlugin
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ServiceBusMessageExtractor implements ToMessageTranslatorInterface
{
    /**
     * @param $aCommandOrEvent
     * @return bool
     */
    public function canTranslateToMessage($aCommandOrEvent)
    {
        return $aCommandOrEvent instanceof ForwardHttpMessage;
    }

    /**
     * @param mixed $aCommandOrEvent
     * @return MessageInterface
     */
    public function translateToMessage($aCommandOrEvent)
    {
        return $aCommandOrEvent->forwardedMessage();
    }
}
 
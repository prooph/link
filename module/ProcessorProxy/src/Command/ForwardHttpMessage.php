<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.01.15 - 21:59
 */

namespace ProcessorProxy\Command;

use Prooph\ServiceBus\Message\MessageInterface;
use Prooph\ServiceBus\Message\MessageNameProvider;
use Prooph\ServiceBus\Message\StandardMessage;

/**
 * Class ForwardHttpMessage
 *
 * Special command that wraps a service bus message received by the http message API..
 * The command does not use a payload array. Instead it holds the reference of the service bus message.
 * In case of forwarding the service bus message to a message dispatcher a special prooph plugin:
 * ProcessorProxy\ProophPlugin\ServiceBusMessageExtractor pulls the service bus message out of the wrapper
 * and forwards it to the dispatcher.
 *
 * @package ProcessorProxy\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ForwardHttpMessage implements MessageNameProvider
{
    /**
     * @var MessageInterface
     */
    private $message;

    /**
     * @param MessageInterface $message
     * @return \ProcessorProxy\Command\ForwardHttpMessage
     */
    public static function createWith(MessageInterface $message)
    {
        return new self($message);
    }

    /**
     * Use ForwardHttpMessage::createWith method when you want
     * to create a new forward message command
     *
     * @param MessageInterface $message
     */
    private function __construct(MessageInterface $message)
    {
        $this->message = $message;
    }

    /**
     * @return MessageInterface
     */
    public function forwardedMessage()
    {
        return $this->message;
    }

    /**
     * @return string Name of the message
     */
    public function getMessageName()
    {
        return __CLASS__;
    }
}
 
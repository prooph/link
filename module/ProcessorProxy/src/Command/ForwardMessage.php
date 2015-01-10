<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.01.15 - 21:59
 */

namespace ProcessorProxy\Command;

use Application\Command\SystemCommand;
use Ginger\Message\LogMessage;
use Ginger\Message\WorkflowMessage;
use Ginger\Processor\Command\StartSubProcess;
use Prooph\ServiceBus\Message\StandardMessage;
use Rhumsaa\Uuid\Uuid;

/**
 * Class ForwardMessage
 *
 * Special command that wraps a ginger message.
 * The command does not use a payload array instead it holds the reference of the ginger message and creates a payload on the fly if necessary.
 *
 * @package ProcessorProxy\Command
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class ForwardMessage extends SystemCommand
{
    /**
     * @var WorkflowMessage|LogMessage|StartSubProcess
     */
    private $message;

    /**
     * @param WorkflowMessage|LogMessage|StartSubProcess $message
     * @return \ProcessorProxy\Command\ForwardMessage
     */
    public static function createFrom($message)
    {
        return new self($message);
    }

    /**
     * Only use the constructor to rebuild the message from a service bus message. Use ForwardMessage::createFrom when you want
     * to create a new forward message command
     *
     * @param WorkflowMessage|LogMessage|StartSubProcess|string $aMessageOrMessageName
     * @param null $aPayload
     * @param int $aVersion
     * @param Uuid $aUuid
     * @param \DateTime $aCreatedOn
     * @throws \InvalidArgumentException
     */
    public function __construct($aMessageOrMessageName, $aPayload = null, $aVersion = 1, Uuid $aUuid = null, \DateTime $aCreatedOn = null)
    {
        //handle message reconstitution
        if (is_string($aMessageOrMessageName)) {
            if (! is_array($aPayload)) throw new \InvalidArgumentException("Payload must be an array when ForwardMessage is rebuild from service bus message");
            if (! array_key_exists("message_type",$aPayload)) throw new \InvalidArgumentException("Payload must contain a message_type when ForwardMessage is rebuild from service bus message");
            if (! array_key_exists("message_payload",$aPayload)) throw new \InvalidArgumentException("Payload must contain message_payload when ForwardMessage is rebuild from service bus message");
            if (! is_array($aPayload["message_payload"])) throw new \InvalidArgumentException("Message payload must be an array when ForwardMessage is rebuild from service bus message");

            $aMessageOrMessageName = $this->rebuildMessage($aPayload['message_type'], $aPayload['message_payload']);
            $aPayload = null;
        }

        if (! $aMessageOrMessageName instanceof WorkflowMessage
            && ! $aMessageOrMessageName instanceof LogMessage
            && ! $aMessageOrMessageName instanceof StartSubProcess) {
            throw new \InvalidArgumentException(sprintf('Invalid message of type %s provided', ((is_object($aMessageOrMessageName))? get_class($aMessageOrMessageName) : gettype($aMessageOrMessageName))));
        }

        $this->message = $aMessageOrMessageName;

        parent::__construct(__CLASS__, $aPayload, $aVersion, $aUuid, $aCreatedOn);
    }

    /**
     * @return WorkflowMessage|LogMessage|StartSubProcess
     */
    public function forwardedMessage()
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function payload()
    {
        return [
            'message_type' => $this->determineMessageType($this->message),
            'message_payload' => $this->convertToPayload($this->message),
        ];
    }

    protected function assertPayload($aPayload = null)
    {
        if (!is_null($aPayload)) throw new \InvalidArgumentException("Payload must be null. The ForwardMessage does not manage a payload array.");
    }

    /**
     * @param $message
     * @return string
     */
    private function determineMessageType($message)
    {
        if ($message instanceof WorkflowMessage) return 'workflow-message';
        if ($message instanceof LogMessage)      return 'log-message';
        if ($message instanceof StartSubProcess) return 'start-sub-process';
    }

    /**
     * @param $message
     * @return array
     */
    private function convertToPayload($message)
    {
        if ($message instanceof WorkflowMessage || $message instanceof LogMessage) {
            return $message->toServiceBusMessage()->toArray();
        }

        if ($message instanceof StartSubProcess) {
            return [
                'start_sub_process_payload' => $message->payload(),
                'start_sub_process_version' => $message->version(),
                'start_sub_process_uuid'    => $message->uuid()->toString(),
                'start_sub_process_created_on' => $message->createdOn()->format(\DateTime::ISO8601),
            ];
        }
    }

    /**
     * @param $messageType
     * @param $messagePayload
     * @return WorkflowMessage|LogMessage|StartSubProcess
     */
    private function rebuildMessage($messageType, array $messagePayload)
    {
        switch ($messageType) {
            case 'workflow-message':
                return WorkflowMessage::fromServiceBusMessage(StandardMessage::fromArray($messagePayload));

            case 'log-message':
                return LogMessage::fromServiceBusMessage(StandardMessage::fromArray($messagePayload));

            case 'start-sub-process':
                return new StartSubProcess(
                    StartSubProcess::MSG_NAME,
                    $messagePayload['start_sub_process_payload'],
                    $messagePayload['start_sub_process_version'],
                    Uuid::fromString($messagePayload['start_sub_process_uuid']),
                    new \DateTime($messagePayload['start_sub_process_created_on'])
                );

        }
    }
}
 
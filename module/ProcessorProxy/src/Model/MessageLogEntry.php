<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.01.15 - 16:50
 */

namespace ProcessorProxy\Model;

use Ginger\Message\GingerMessage;
use Ginger\Message\LogMessage;
use Ginger\Message\ProophPlugin\ToGingerMessageTranslator;
use Ginger\Message\WorkflowMessage;
use Ginger\Processor\Command\StartSubProcess;
use Ginger\Processor\Event\SubProcessFinished;
use Ginger\Processor\Process;
use Ginger\Processor\ProcessId;
use Ginger\Processor\Task\TaskListPosition;
use Prooph\ServiceBus\Message\MessageInterface;
use Rhumsaa\Uuid\Uuid;

/**
 * Class MessageLogEntry
 *
 * @package ProcessorProxy\Model
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class MessageLogEntry 
{
    /**
     * @var Uuid
     */
    private $messageId;

    /**
     * @var string
     */
    private $messageName;

    /**
     * @var int
     */
    private $version;

    /**
     * @var \DateTime
     */
    private $loggedAt;

    /**
     * All ginger messages - except start messages - are connected with a process task
     * So the TaskListPosition will only be null when a start message was logged.
     *
     * @var null|TaskListPosition
     */
    private $taskListPosition;

    /**
     * A start message is neither connect with a task nor with a process
     * But the DbalMessageLogger listens on the Ginger\Processor\Processor events to determine
     * the process id of the process triggered by the start message
     * and adds the process id to the message log entry.
     *
     * @var null|ProcessId
     */
    private $processId;

    /**
     * @var MessageStatus
     */
    private $status;

    /**
     * @param MessageInterface|GingerMessage $message
     * @throws \InvalidArgumentException
     * @return MessageLogEntry
     */
    public static function logMessage($message)
    {
        if ($message instanceof MessageInterface) {
            $message = (new ToGingerMessageTranslator())->translateToGingerMessage($message);
        }

        if ($message instanceof WorkflowMessage)    return self::logWorkflowMessage($message);
        if ($message instanceof LogMessage)         return self::logLogMessage($message);
        if ($message instanceof StartSubProcess)    return self::logStartSubProcess($message);
        if ($message instanceof SubProcessFinished) return self::logSubProcessFinished($message);

        throw new \InvalidArgumentException(sprintf('Invalid message type given: %s', ((is_object($message)? get_class($message) : gettype($message)))));
    }

    /**
     * @param WorkflowMessage $message
     * @return MessageLogEntry
     */
    public static function logWorkflowMessage(WorkflowMessage $message)
    {
        return self::createFromMessageProps($message->uuid(), $message->messageName(), $message->version(), $message->processTaskListPosition());
    }

    /**
     * @param LogMessage $message
     * @return MessageLogEntry
     */
    public static function logLogMessage(LogMessage $message)
    {
        return self::createFromMessageProps($message->uuid(), $message->messageName(), 1, $message->processTaskListPosition());
    }

    /**
     * @param StartSubProcess $message
     * @return MessageLogEntry
     */
    public static function logStartSubProcess(StartSubProcess $message)
    {
        return self::createFromMessageProps($message->uuid(), $message->messageName(), $message->version(), $message->parentTaskListPosition());
    }

    /**
     * @param SubProcessFinished $message
     * @return MessageLogEntry
     */
    public static function logSubProcessFinished(SubProcessFinished $message)
    {
        return self::createFromMessageProps($message->uuid(), $message->messageName(), $message->version(), $message->lastMessage()->processTaskListPosition());
    }

    /**
     * @param array $messageLogEntryArr
     * @return MessageLogEntry
     * @throws \InvalidArgumentException
     */
    public static function fromArray(array $messageLogEntryArr)
    {
        if (! array_key_exists("message_id", $messageLogEntryArr)) throw new \InvalidArgumentException('Message id missing in status array');
        if (! array_key_exists("message_name", $messageLogEntryArr)) throw new \InvalidArgumentException('Message name missing in status array');
        if (! array_key_exists("version", $messageLogEntryArr)) throw new \InvalidArgumentException('Message version missing in status array');
        if (! array_key_exists("logged_at", $messageLogEntryArr)) throw new \InvalidArgumentException('Message logged at missing in status array');
        if (! array_key_exists("task_list_position", $messageLogEntryArr)) throw new \InvalidArgumentException('Message task list position missing in status array');
        if (! array_key_exists("process_id", $messageLogEntryArr)) throw new \InvalidArgumentException('Message process id missing in status array');
        if (! array_key_exists("status", $messageLogEntryArr)) throw new \InvalidArgumentException('Message status missing in status array');
        if (! array_key_exists("failure_msg", $messageLogEntryArr)) throw new \InvalidArgumentException('Status failure msg position on missing in status array');

        $status = MessageStatus::fromArray($messageLogEntryArr);

        $taskListPosition = (is_null($messageLogEntryArr["task_list_position"]))? null : TaskListPosition::fromString($messageLogEntryArr["task_list_position"]);
        $processId = (is_null($messageLogEntryArr["process_id"]))? null : ProcessId::fromString($messageLogEntryArr["process_id"]);

        return new self(
            Uuid::fromString($messageLogEntryArr["message_id"]),
            $messageLogEntryArr["message_name"],
            (int)$messageLogEntryArr["version"],
            \DateTime::createFromFormat('Y-m-d\TH:i:s.uO', $messageLogEntryArr["logged_at"]),
            $status,
            $taskListPosition,
            $processId
        );
    }

    /**
     * @param Uuid $messageId
     * @param string $messageName
     * @param int $version
     * @param null|TaskListPosition $taskListPosition
     * @return MessageLogEntry
     */
    private static function createFromMessageProps(Uuid $messageId, $messageName, $version, TaskListPosition $taskListPosition = null)
    {
        return new self($messageId, $messageName, $version, new \DateTime(), MessageStatus::pending(), $taskListPosition);
    }

    /**
     * @param Uuid $messageId
     * @param $messageName
     * @param $version
     * @param \DateTime $loggedAt
     * @param MessageStatus $messageStatus
     * @param null|TaskListPosition $taskListPosition
     * @param null|ProcessId $processId
     */
    private function __construct(
        Uuid $messageId,
        $messageName,
        $version,
        \DateTime $loggedAt,
        MessageStatus $messageStatus,
        TaskListPosition $taskListPosition = null,
        ProcessId $processId = null)
    {
        $this->setMessageId($messageId);
        $this->setMessageName($messageName);
        $this->setVersion($version);
        $this->setLoggedAt($loggedAt);
        $this->setStatus($messageStatus);

        //ProcessId of TaskListPosition is used when TaskListPosition is not null
        if (! is_null($taskListPosition)) {
            $this->setTaskListPosition($taskListPosition);
        } else {
            if (! is_null($processId)) $this->setProcessId($processId);
        }

    }

    /**
     * @return \Rhumsaa\Uuid\Uuid
     */
    public function messageId()
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function messageName()
    {
        return $this->messageName;
    }

    /**
     * @return int
     */
    public function version()
    {
        return $this->version;
    }

    /**
     * @return \DateTime
     */
    public function loggedAt()
    {
        return $this->loggedAt;
    }

    /**
     * @return TaskListPosition
     */
    public function taskListPosition()
    {
        return $this->taskListPosition;
    }

    /**
     * @return null|ProcessId
     */
    public function processId()
    {
        return $this->processId;
    }

    /**
     * @return \ProcessorProxy\Model\MessageStatus
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function wasAStartMessageLogged()
    {
        return $this->taskListPosition() === null;
    }

    /**
     * @throws \RuntimeException
     * @return void
     */
    public function markAsSucceed()
    {
        if (is_null($this->processId)) {
            throw new \RuntimeException(sprintf('Log entry for message %s can not be marked as complete. It was no process id assigned.', $this->messageId()->toString()));
        }

        $this->status = $this->status()->markAsSucceed();
    }

    /**
     * @param $failureMsg
     */
    public function markAsFailed($failureMsg)
    {
        $this->status = $this->status()->markAsFailed($failureMsg);
    }

    /**
     * Can only be used if logged message was a start message
     * and process id can only be assigned once!
     * The MessageLogEntry is automatically marked as succeed.
     *
     * @param ProcessId $processId
     */
    public function assignIdOfStartedProcess(ProcessId $processId)
    {
        $this->setProcessId($processId);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'message_id' => $this->messageId()->toString(),
            'message_name' => $this->messageName(),
            'version' => $this->version(),
            'logged_at' => $this->loggedAt()->format('Y-m-d\TH:i:s.uO'),
            'task_list_position' => $this->taskListPosition()? $this->taskListPosition()->toString() : null,
            'process_id' => $this->processId()? $this->processId()->toString() : null,
            'status' => $this->status()->toString(),
            'failure_msg' => $this->status()->failureMsg()
        ];
    }

    /**
     * @param \DateTime $loggedOn
     */
    private function setLoggedAt(\DateTime $loggedOn)
    {
        $this->loggedAt = $loggedOn;
    }

    /**
     * @param \Rhumsaa\Uuid\Uuid $messageId
     */
    private function setMessageId(Uuid $messageId)
    {
        $this->messageId = $messageId;
    }

    /**
     * @param string $messageName
     * @throws \InvalidArgumentException
     */
    private function setMessageName($messageName)
    {
        if (! is_string($messageName) || empty($messageName)) throw new \InvalidArgumentException('Message name must be a non empty string');
        $this->messageName = $messageName;
    }

    /**
     * @param TaskListPosition $taskListPosition
     */
    private function setTaskListPosition(TaskListPosition $taskListPosition)
    {
        $this->taskListPosition = $taskListPosition;
        $this->setProcessId($taskListPosition->taskListId()->processId());
    }

    /**
     * @param ProcessId $processId
     * @throws \InvalidArgumentException
     */
    private function setProcessId(ProcessId $processId)
    {
        if (! is_null($this->processId)) throw new \InvalidArgumentException(sprintf('Process id %s can not be logged. Message log already has a process id %s', $processId->toString(), $this->processId()->toString()));

        $this->processId = $processId;
    }

    /**
     * @param \ProcessorProxy\Model\MessageStatus $status
     */
    private function setStatus(MessageStatus $status)
    {
        $this->status = $status;
    }

    /**
     * @param int $version
     * @throws \InvalidArgumentException
     */
    private function setVersion($version)
    {
        if (! is_int($version) || $version <= 0) throw new \InvalidArgumentException('Version must be greater than zero');
        $this->version = $version;
    }
}
 
<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12.01.15 - 16:51
 */

namespace ProcessorProxy\Model;

/**
 * Class MessageStatus
 *
 * @package ProcessorProxy\Model
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class MessageStatus 
{
    const PENDING = "pending";
    const SUCCEED = "succeed";
    const FAILED  = "failed";

    /**
     * @var string
     */
    private $status;

    /**
     * @var string|null
     */
    private $failureMsg;

    /**
     * @return MessageStatus
     */
    public static function pending()
    {
        return new self(self::PENDING);
    }

    /**
     * @param array $statusArr
     * @return MessageStatus
     * @throws \InvalidArgumentException
     */
    public static function fromArray(array $statusArr)
    {
        if (! array_key_exists("status", $statusArr)) throw new \InvalidArgumentException('Status missing in status array');
        if (! array_key_exists("failure_msg", $statusArr)) throw new \InvalidArgumentException('Failure msg missing in status array');
        return new self($statusArr['status'], $statusArr['failure_msg']);
    }

    /**
     * @param string $status
     * @param null|string $failureMsg
     * @throws \InvalidArgumentException
     */
    private function __construct($status, $failureMsg = null)
    {
        if (! in_array($status, [self::PENDING, self::SUCCEED, self::FAILED])) throw new \InvalidArgumentException('Message status is invalid');

        if ($status === self::FAILED || !is_null($failureMsg)) {
            if (! is_string($failureMsg) || empty($failureMsg)) throw new \InvalidArgumentException('Failure msg must be a not empty string when status is failed');
        }

        $this->status = $status;
        $this->failureMsg = $failureMsg;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->toString() === self::PENDING;
    }

    /**
     * @return bool
     */
    public function isSucceed()
    {
        return $this->toString() === self::SUCCEED;
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return $this->toString() === self::FAILED;
    }

    /**
     * @return MessageStatus
     * @throws \InvalidArgumentException
     */
    public function markAsSucceed()
    {
        if (! $this->isPending()) throw new \InvalidArgumentException('Can not mark status as succeed. It is not pending');

        return new self(self::SUCCEED);
    }

    /**
     * @param string $failureMsg
     * @return MessageStatus
     * @throws \InvalidArgumentException
     */
    public function markAsFailed($failureMsg)
    {
        if (! $this->isPending()) throw new \InvalidArgumentException('Can not mark status as failed. It is not pending');

        return new self(self::FAILED, $failureMsg);
    }

    /**
     * @return string|null
     */
    public function failureMsg()
    {
        return $this->failureMsg;
    }

    /**
     * @param MessageStatus $status
     * @return bool
     */
    public function equals(MessageStatus $status)
    {
        return $this->toString() === $status->toString();
    }
}
 
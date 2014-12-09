<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.12.14 - 19:45
 */

namespace Application\Event;

use Codeliner\ArrayReader\ArrayReader;
use Prooph\EventSourcing\DomainEvent;
use Rhumsaa\Uuid\Uuid;

/**
 * Event SystemChanged
 *
 * Basic domain event implementation for all events that represent a change in the system that is not related
 * to a specific aggregate. Event sourced aggregates record their own type of event (Prooph\EventSourcing\AggregateChanged)
 *
 * @package Application\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class SystemChanged implements DomainEvent
{
    /**
     * @var Uuid
     */
    protected $uuid;

    /**
     * This property is injected via Reflection
     *
     * @var int
     */
    protected $version;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @var \DateTime
     */
    protected $occurredOn;

    /**
     * @var ArrayReader
     */
    private $payloadReader;

    /**
     * @param array $payload
     * @return static
     */
    public static function occur(array $payload)
    {
        return new static($payload);
    }

    /**
     * @param array $payload
     * @param Uuid $uuid
     * @param \DateTime $occurredOn
     * @param $version
     * @return static
     */
    public static function reconstitute(array $payload, Uuid $uuid, \DateTime $occurredOn, $version)
    {
        return new static($payload, $uuid, $occurredOn, $version);
    }

    /**
     * @param array $payload
     * @param Uuid $uuid
     * @param \DateTime $occurredOn
     */
    protected function __construct(array $payload, Uuid $uuid = null, \DateTime $occurredOn = null)
    {
        if (is_null($uuid)) {
            $uuid = Uuid::uuid4();
        }

        if (is_null($occurredOn)) {
            $occurredOn = new \DateTime();
        }

        $this->payload     = $payload;
        $this->uuid        = $uuid;
        $this->occurredOn  = $occurredOn;
    }

    /**
     * @return Uuid
     */
    public function uuid()
    {
        return $this->uuid;
    }

    /**
     * @return \DateTime
     */
    public function occurredOn()
    {
        return $this->occurredOn;
    }

    /**
     * @return array
     */
    public function payload()
    {
        return $this->payload;
    }

    /**
     * @return ArrayReader
     */
    public function toPayloadReader()
    {
        if (is_null($this->payloadReader)) {
            $this->payloadReader = new ArrayReader($this->payload());
        }

        return $this->payloadReader;
    }
}
 
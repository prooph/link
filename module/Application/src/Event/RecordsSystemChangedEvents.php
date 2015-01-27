<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.12.14 - 20:55
 */

namespace Application\Event;

/**
 * Trait RecordsSystemChangedEvents
 *
 * Basic implementation to add the functionality to record SystemChanged events
 *
 * @package Application\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
trait RecordsSystemChangedEvents
{
    /**
     * @var SystemChanged[]
     */
    private $recordedEvents = [];

    /**
     * @param SystemChanged $event
     */
    protected function recordThat(SystemChanged $event)
    {
        $this->recordedEvents[] = $event;
    }

    /**
     * @return SystemChanged[]
     */
    public function popRecordedEvents()
    {
        $events = $this->recordedEvents;

        $this->recordedEvents = [];

        return $events;
    }
} 
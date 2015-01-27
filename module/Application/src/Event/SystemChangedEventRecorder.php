<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.12.14 - 21:02
 */

namespace Application\Event;

/**
 * Interface SystemChangedEventRecorder
 *
 * @package Application\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface SystemChangedEventRecorder 
{
    /**
     * @return SystemChanged[]
     */
    public function popRecordedEvents();
}
 
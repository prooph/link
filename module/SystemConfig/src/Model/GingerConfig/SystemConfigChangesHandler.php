<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.01.15 - 21:56
 */

namespace SystemConfig\Model\GingerConfig;

use Prooph\ServiceBus\EventBus;
use SystemConfig\Model\ConfigWriter;
use SystemConfig\Model\GingerConfig;
use SystemConfig\Service\HandlesSystemConfigChanges;

/**
 * Class SystemConfigChangesHandler
 *
 * @package SystemConfig\Model\GingerConfig
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class SystemConfigChangesHandler implements HandlesSystemConfigChanges
{
    /**
     * @var EventBus
     */
    protected $eventBus;

    /**
     * @var ConfigWriter
     */
    protected $configWriter;

    public function publishChanges(GingerConfig $gingerConfig)
    {
        foreach ($gingerConfig->popRecordedEvents() as $recordedEvent) $this->eventBus->dispatch($recordedEvent);
    }

    /**
     * @param ConfigWriter $configWriter
     * @return void
     */
    public function setConfigWriter(ConfigWriter $configWriter)
    {
        $this->configWriter = $configWriter;
    }

    /**
     * @param EventBus $eventBus
     * @return void
     */
    public function setEventBus(EventBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }
}
 
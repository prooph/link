<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/8/14 - 10:53 PM
 */
namespace SystemConfig\Model\GingerConfig;

use Prooph\ServiceBus\EventBus;
use SystemConfig\Command\ChangeNodeName;
use SystemConfig\Model\ConfigWriter;
use SystemConfig\Model\GingerConfig;

/**
 * Class ChangeNodeNameHandler
 *
 * @package SystemConfig\Model\GingerConfig
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class ChangeNodeNameHandler 
{
    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @param ConfigWriter $configWriter
     * @param EventBus $eventBus
     */
    public function __construct(ConfigWriter $configWriter, EventBus $eventBus)
    {
        $this->configWriter = $configWriter;
        $this->eventBus = $eventBus;
    }

    /**
     * @param ChangeNodeName $command
     */
    public function handle(ChangeNodeName $command)
    {
        $gingerConfig = GingerConfig::initializeFromConfigLocation($command->configLocation());

        $gingerConfig->changeNodeName($command->newNodeName(), $this->configWriter);

        foreach($gingerConfig->popRecordedEvents() as $event) $this->eventBus->dispatch($event);
    }
} 
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.01.15 - 21:51
 */

namespace SystemConfig\Service;

use Prooph\ServiceBus\EventBus;
use SystemConfig\Model\ConfigWriter;

/**
 * Interface HandlesSystemConfigChanges
 *
 * @package SystemConfig\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface HandlesSystemConfigChanges
{
    /**
     * @param ConfigWriter $configWriter
     * @return void
     */
    public function setConfigWriter(ConfigWriter $configWriter);

    /**
     * @param EventBus $eventBus
     * @return void
     */
    public function setEventBus(EventBus $eventBus);
}
 
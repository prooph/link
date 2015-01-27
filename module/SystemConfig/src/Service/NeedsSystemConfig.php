<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 30.12.14 - 16:45
 */

namespace SystemConfig\Service;

use SystemConfig\Projection\GingerConfig;

/**
 * Interface NeedsSystemConfig
 *
 * @package SystemConfig\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface NeedsSystemConfig
{
    /**
     * @param GingerConfig $systemConfig
     * @return void
     */
    public function setSystemConfig(GingerConfig $systemConfig);
}
 
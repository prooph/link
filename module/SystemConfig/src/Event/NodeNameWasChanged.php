<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.12.14 - 23:02
 */

namespace SystemConfig\Event;

use Application\Event\SystemChanged;
use Ginger\Processor\NodeName;

/**
 * Event NodeNameWasChanged
 *
 * @package SystemConfig\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class NodeNameWasChanged extends SystemChanged
{
    /**
     * @param NodeName $newName
     * @param NodeName $oldName
     * @return NodeNameWasChanged
     */
    public static function to(NodeName $newName, NodeName $oldName)
    {
        return self::occur(['old_name' => $oldName->toString(), 'new_name' => $newName->toString()]);
    }

    /**
     * @return NodeName
     */
    public function oldNodeName()
    {
        return NodeName::fromString($this->payload['old_name']);
    }

    /**
     * @return NodeName
     */
    public function newNodeName()
    {
        return NodeName::fromString($this->payload['new_name']);
    }
}
 
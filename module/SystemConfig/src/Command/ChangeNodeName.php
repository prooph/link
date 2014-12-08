<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/8/14 - 7:23 PM
 */
namespace SystemConfig\Command;

/**
 * Command ChangeNodeName
 *
 * @package SystemConfig\Command
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class ChangeNodeName extends AbstractCommand
{
    /**
     * @param $newNodeName
     * @return ChangeNodeName
     * @throws \InvalidArgumentException
     */
    public static function to($newNodeName)
    {
        return new self(__CLASS__, ['node_name' => $newNodeName]);
    }

    /**
     * @return string
     */
    public function newNodeName()
    {
        return $this->payload['node_name'];
    }

    /**
     * @param null|array $aPayload
     * @throws \InvalidArgumentException
     */
    protected function assertPayload($aPayload = null)
    {
        if (! is_array($aPayload) || ! array_key_exists('node_name', $aPayload)) {
            throw new \InvalidArgumentException('Payload does not contain a node_name');
        }

        if (! is_string($aPayload['node_name'])) throw new \InvalidArgumentException('Node name must be string');
    }
}
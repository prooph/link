<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/8/14 - 7:28 PM
 */
namespace SystemConfig\Command;

use Prooph\ServiceBus\Command;
use Rhumsaa\Uuid\Uuid;

/**
 * Class AbstractCommand
 *
 * @package SystemConfig\Command
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
abstract class AbstractCommand extends Command
{
    /**
     * @throws \BadMethodCallException
     * @return Command
     */
    public static function getNew()
    {
        throw new \BadMethodCallException('Calling Command::getNew is not allowed!');
    }

    /**
     * @param mixed $aPayload
     * @throws \BadMethodCallException
     * @return Command
     */
    public static function fromPayload($aPayload)
    {
        throw new \BadMethodCallException('Calling Command::fromPayload is not allowed!');
    }

    /**
     * @param string $aMessageName
     * @param null $aPayload
     * @param int $aVersion
     * @param Uuid $aUuid
     * @param \DateTime $aCreatedOn
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function __construct($aMessageName, $aPayload = null, $aVersion = 1, Uuid $aUuid = null, \DateTime $aCreatedOn = null)
    {
        $this->assertPayload($aPayload);
        parent::__construct($aMessageName, $aPayload, $aVersion, $aUuid, $aCreatedOn);
    }

    abstract protected function assertPayload($aPayload = null);
} 
<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 15.11.14 - 23:27
 */

namespace FileConnectorTest\Mock;

use Prooph\Processing\Processor\WorkflowProcessor;

/**
 * Class StupidWorkflowProcessorMock
 *
 * @package ProcessingTest\Mock
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class StupidWorkflowProcessorMock extends WorkflowProcessor
{
    /**
     * @var mixed
     */
    private $lastReceivedMessage;


    public function __construct()
    {
    }

    public function receiveMessage($message)
    {
        $this->lastReceivedMessage = $message;
    }

    public function getLastReceivedMessage()
    {
        return $this->lastReceivedMessage;
    }

    public function reset()
    {
        $this->lastReceivedMessage = null;
    }
}
 
<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 18:57
 */

namespace FileConnector\Service;

use Ginger\Environment\Factory\WorkflowProcessorFactory;
use Ginger\Message\LogMessage;
use Ginger\Message\MessageNameUtils;
use Ginger\Message\WorkflowMessage;
use Ginger\Message\WorkflowMessageHandler;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Zend\Stdlib\ErrorHandler;

/**
 * Class FileGateway
 *
 * Ginger workflow message handler to write data to file or read data from file
 *
 * @package FileConnector\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FileGateway implements WorkflowMessageHandler
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var EventBus
     */
    private $eventBus;

    private $fileHandlerFactory;

    /**
     * @param WorkflowMessage $aWorkflowMessage
     * @return void
     */
    public function handleWorkflowMessage(WorkflowMessage $aWorkflowMessage)
    {
        try {
            switch ($aWorkflowMessage->getMessageType()) {
                case MessageNameUtils::COLLECT_DATA:
                    $this->collectData($aWorkflowMessage);
                    break;
                case MessageNameUtils::PROCESS_DATA:

                    break;
                default:
                    $this->eventBus->dispatch(LogMessage::logUnsupportedMessageReceived($aWorkflowMessage, 'fileconnector'));
            }
        } catch (\Exception $ex) {
            ErrorHandler::stop();
            $this->eventBus->dispatch(LogMessage::logException($ex, $aWorkflowMessage->getProcessTaskListPosition()));
        }
    }

    /**
     * @param WorkflowMessage $workflowMessage
     * @throws \InvalidArgumentException
     */
    private function collectData(WorkflowMessage $workflowMessage)
    {
        $metadata = $workflowMessage->getMetadata();

        if (! isset($metadata['filename_pattern'])) throw new \InvalidArgumentException("Missing filename_pattern in metadata");
        if (! isset($metadata['file_type'])) throw new \InvalidArgumentException("Missing file_type in metadata");
        if (! isset($metadata['path'])) throw new \InvalidArgumentException("Missing path in metadata");
        if (! is_dir($metadata['path'])) throw new \InvalidArgumentException(sprintf('Directory %s is invalid', $metadata['path']));
        if (! is_readable($metadata['path'])) throw new \InvalidArgumentException(sprintf('Directory %s is not readable', $metadata['path']));

        ErrorHandler::start();

        $fileNames = array_filter(
            scandir($metadata['path']),
            function ($filename) use ($metadata) {
                return (bool)preg_match($metadata['filename_pattern'], $filename);
            }
        );

        $dataTypeOfFile = (isset($metadata['file_data_type']))? $metadata['file_data_type'] : $workflowMessage->getPayload()->getTypeClass();



        ErrorHandler::stop(true);


    }

    /**
     * Register command bus that can be used to send new commands to the workflow processor
     *
     * @param CommandBus $commandBus
     * @return void
     */
    public function useCommandBus(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * Register event bus that can be used to send events to the workflow processor
     *
     * @param EventBus $eventBus
     * @return void
     */
    public function useEventBus(EventBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }
}
 
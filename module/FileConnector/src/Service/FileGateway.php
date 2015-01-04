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

use Assert\Assertion;
use FileConnector\Service\FileTypeAdapter\FileTypeAdapterManager;
use Ginger\Message\LogMessage;
use Ginger\Message\MessageNameUtils;
use Ginger\Message\WorkflowMessage;
use Ginger\Message\WorkflowMessageHandler;
use Ginger\Type\Description\NativeType;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Zend\Stdlib\ArrayUtils;
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
    const FETCH_MODE_SINGLE_FILE = 'single_file';
    const FETCH_MODE_MULTI_FILES = 'multi_files';

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var FileTypeAdapterManager
     */
    private $fileTypeAdapters;

    /**
     * @param FileTypeAdapterManager $fileTypeAdapters
     */
    public function __construct(FileTypeAdapterManager $fileTypeAdapters)
    {
        $this->fileTypeAdapters = $fileTypeAdapters;
    }

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
     * This method provides different modes depending on metadata configuration and matched files
     *
     * Mode 1: Metadata: fetch_mode = 'single_file'
     * @param WorkflowMessage $workflowMessage
     * @throws \InvalidArgumentException
     */
    private function collectData(WorkflowMessage $workflowMessage)
    {
        $metadata = $workflowMessage->getMetadata();

        if (! isset($metadata['filename_pattern'])) throw new \InvalidArgumentException("Missing filename_pattern in metadata");
        if (! isset($metadata['file_type'])) throw new \InvalidArgumentException("Missing file_type in metadata");
        if (! isset($metadata['path'])) throw new \InvalidArgumentException("Missing path in metadata");

        $metadata['path'] = rtrim(realpath($metadata['path']), DIRECTORY_SEPARATOR);

        if (! is_dir($metadata['path'])) throw new \InvalidArgumentException(sprintf('Directory %s is invalid', $metadata['path']));
        if (! is_readable($metadata['path'])) throw new \InvalidArgumentException(sprintf('Directory %s is not readable', $metadata['path']));


        /** @var $fileHandler FileTypeAdapter */
        $fileHandler = $this->fileTypeAdapters->get($metadata['file_type']);

        ErrorHandler::start();

        $fileNames = array_map(
            function($filename) use ($metadata) {
                return $metadata['path'] . DIRECTORY_SEPARATOR . $filename;
            },
            array_values(
                array_filter(
                    //We use scandir with descending order, so that files which include a date in the filename are sorted to newest first
                    scandir($metadata['path'], 1),
                    function ($filename) use ($metadata) {
                        return (bool)preg_match($metadata['filename_pattern'], $filename);
                    }
                )
            )
        );

        ErrorHandler::stop(true);

        $fetchMode = isset($metadata['fetch_mode'])? $metadata['fetch_mode'] : self::FETCH_MODE_SINGLE_FILE;

        if (count($fileNames)) {
            if ($fetchMode === self::FETCH_MODE_SINGLE_FILE) {
                $this->collectDataFromSingleFile($fileNames[0], $workflowMessage, $fileHandler);
                return;
            } elseif ($fetchMode === self::FETCH_MODE_MULTI_FILES){
                $this->collectDataFromMultipleFiles($fileNames, $workflowMessage, $fileHandler);
                return;
            } else {
                throw new \InvalidArgumentException("Metadata contains unknown fetch_mode");
            }
        } else {
            if ($fetchMode === self::FETCH_MODE_SINGLE_FILE) {
                throw new \InvalidArgumentException(sprintf("No file found for filename pattern %s", $metadata['filename_pattern']));
            } else {
                $typeClass = $workflowMessage->getPayload()->getTypeClass();

                if ($typeClass::prototype()->typeDescription()->nativeType() !== NativeType::COLLECTION) {
                    throw new \InvalidArgumentException(sprintf("Filename pattern %s matches no file and the requested ginger type %s is not a collection.", $metadata['filename_pattern'], $typeClass));
                }

                $metadata['total_items'] = 0;

                $this->eventBus->dispatch($workflowMessage->answerWith($typeClass::fromNativeValue([]), $metadata));
                return;
            }
        }
    }

    private function collectDataFromSingleFile($filename, WorkflowMessage $workflowMessage, FileTypeAdapter $fileHandler)
    {
        $typeClass = $workflowMessage->getPayload()->getTypeClass();

        $metadata = $workflowMessage->getMetadata();

        $data = $fileHandler->readDataForType($filename, $typeClass::prototype(), $metadata);

        $this->eventBus->dispatch($workflowMessage->answerWith($typeClass::fromNativeValue($data), $metadata));
    }

    private function collectDataFromMultipleFiles($fileNames, WorkflowMessage $workflowMessage, FileTypeAdapter $fileHandler)
    {
        $metadata = $workflowMessage->getMetadata();

        $fileDataType = (isset($metadata['file_data_type']))? $metadata['file_data_type'] : $workflowMessage->getPayload()->getTypeClass();

        Assertion::implementsInterface($fileDataType, 'Ginger\Type\Type');

        $fileDataPrototype = $fileDataType::prototype();

        $fileDataCollection = [];
        $collectedData = null;
        $typeClass = $workflowMessage->getPayload()->getTypeClass();

        foreach ($fileNames as $filename) {
            $fileDataCollection[] = $fileHandler->readDataForType($filename, $fileDataPrototype, $metadata);
        }

        if (isset($metadata['merge_files']) && $metadata['merge_files']) {
            $mergedFileData = [];

            foreach ($fileDataCollection as $fileData) {
                if (! is_array($fileData)) {
                    $fileData = [$fileData];
                }

                $mergedFileData = ArrayUtils::merge($mergedFileData, $fileData);
            }

            $collectedData = $typeClass::fromNativeValue($mergedFileData);
        } else {

            if ($typeClass::prototype()->typeDescription()->nativeType() !== NativeType::COLLECTION) {
                throw new \InvalidArgumentException(sprintf("Filename pattern %s matches multiple files but the requested ginger type %s is not a collection.", $metadata['filename_pattern'], $typeClass));
            }

            $metadata['total_items'] = count($fileDataCollection);

            $collectedData = $typeClass::fromNativeValue($fileDataCollection);
        }

        $this->eventBus->dispatch($workflowMessage->answerWith($collectedData, $metadata));
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
 
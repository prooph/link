<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.01.15 - 18:57
 */

namespace FileConnector\Service;

use Application\SharedKernel\LocationTranslator;
use Assert\Assertion;
use FileConnector\Service\FileTypeAdapter\FileTypeAdapterManager;
use Prooph\Processing\Message\AbstractWorkflowMessageHandler;
use Prooph\Processing\Message\ProcessingMessage;
use Prooph\Processing\Message\LogMessage;
use Prooph\Processing\Message\MessageNameUtils;
use Prooph\Processing\Message\WorkflowMessage;
use Prooph\Processing\Message\WorkflowMessageHandler;
use Prooph\Processing\Type\AbstractDictionary;
use Prooph\Processing\Type\Description\NativeType;
use Prooph\Processing\Type\SingleValue;
use Prooph\Processing\Type\Type;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\ErrorHandler;

/**
 * Class FileGateway
 *
 * Processing message handler to write data to file or read data from file
 *
 * @package FileConnector\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FileGateway extends AbstractWorkflowMessageHandler
{
    /**
     * Required key in metadata (if location is not present). Defines the path where file(s) can be found or written to
     */
    const META_PATH               = 'path';

    /**
     * Optional key in metadata. Can be defined instead of path is translated into a path via Application\SharedKernel\LocationTranslator
     */
    const META_LOCATION           = 'location';

    /**
     * Required key in metadata. Defines the type of the file. The key must match with one of the registered FileTypeAdapters
     */
    const META_FILE_TYPE          = 'file_type';

    /**
     * Optional key in collect-data metadata. Defaults to single_file if not present
     */
    const META_FETCH_MODE        = 'fetch_mode';
    /**
     * Default option for fetch_mode. Indicates that only data from one file should be collected.
     * If filename_pattern matches multiple files only the data of the first file is loaded.
     * Files are sorted alphabetical descending. So if filename_pattern includes a date in the format Y-m-d
     * data from the newest file should be loaded
     */
    const META_FETCH_MODE_SINGLE_FILE = 'single_file';

    /**
     * If requested processing type is a collection and filename_pattern matches multiple files each file
     * can be loaded as an item for the collection. In this case you should provide a file_data_type pointing
     * to the processing type that should be used to load the data of each file.
     */
    const META_FETCH_MODE_MULTI_FILES = 'multi_files';

    /**
     * Required key in collect-data metadata. Regex pattern that should be used to detect files to load.
     */
    const META_FILENAME_PATTERN   = 'filename_pattern';

    /**
     * Optional key in collect-data metadata. Useful when using the multi_files fetch_mode. It tells the FileGateway which processing type
     * should be used when loading a file.
     */
    const META_FILE_DATA_TYPE     = 'file_data_type';

    /**
     * Optional key in collect-data metadata. If set to TRUE and filename_pattern matches multiple files the FileGateway merges
     * the data from each file into a single array and creates the requested processing type with the merged data array
     */
    const META_MERGE_FILES        = 'merge_files';

    /**
     * Required key in process-data metadata. Specifies the filename for the file in which the data should be written.
     * The filename_template is rendered with a FileNameRenderer that uses a mustache engine to resolve placeholders
     * in the filename_template. In the template are various dynamic values available like the metadata array, the data
     * of the processing type that is going to be written to file and general mixins.
     * see: module/FileConnector/config/module.config.php for default mixins and how to provide your own ones.
     */
    const META_FILENAME_TEMPLATE  = 'filename_template';

    /**
     * Optional key in process-data metadata. If set to TRUE and given processing type is a collection each item of the
     * collection is written to a single file by using the filename_template and provide the data of the item within the
     * template so that you can create unique file names.
     */
    const META_WRITE_MULTI_FILES  = 'write_multi_files';

    /**
     * When multi_files fetch_mode is used to collect data for a collection from multiple files the FileGateway sets
     * the number of loaded items in the responds metadata.
     */
    const META_TOTAL_ITEMS = 'total_items';

    /**
     * Key to access metadata in a filename_template f.e.: {{metadata.date_format}}
     */
    const FILENAME_DATA_METADATA   = 'metadata';

    /**
     * When processing type is a dictionary its data is passed to the filename_template accessible via the data key.
     * When using the write_multi_files write mode and the processing type collection contains dictionary items each
     * item is passed to the filename_template and can be accessed via data.
     * Example: {{data.id]]
     */
    const FILENAME_DATA_DATA       = 'data';

    /**
     * When processing type is a single value you can access it in the filename_template via the value key.
     * Example: {{value}}
     */
    const FILENAME_DATA_VALUE      = 'value';

    /**
     * When using the write_multi_files write mode you can access the index of the current collection item with this key
     * Example: {{item_index}}
     */
    const FILENAME_DATA_ITEM_INDEX = 'item_index';

    /**
     * @var FileTypeAdapterManager
     */
    private $fileTypeAdapters;

    /**
     * @var FileNameRenderer
     */
    private $fileNameRenderer;

    /**
     * @var LocationTranslator
     */
    private $locationTranslator;

    /**
     * @param FileTypeAdapterManager $fileTypeAdapters
     * @param FileNameRenderer $fileNameRenderer
     * @param LocationTranslator $locationTranslator
     */
    public function __construct(FileTypeAdapterManager $fileTypeAdapters, FileNameRenderer $fileNameRenderer, LocationTranslator $locationTranslator)
    {
        $this->fileTypeAdapters   = $fileTypeAdapters;
        $this->fileNameRenderer   = $fileNameRenderer;
        $this->locationTranslator = $locationTranslator;
    }

    /**
     * If workflow message handler receives a collect-data message it forwards the message to this
     * method and uses the returned ProcessingMessage as response
     *
     * @param WorkflowMessage $workflowMessage
     * @return ProcessingMessage
     */
    protected function handleCollectData(WorkflowMessage $workflowMessage)
    {
        try {
            return $this->collectData($workflowMessage);
        } catch (\Exception $ex) {
            ErrorHandler::stop();
            return LogMessage::logException($ex, $workflowMessage);
        }
    }

    /**
     * If workflow message handler receives a process-data message it forwards the message to this
     * method and uses the returned ProcessingMessage as response
     *
     * @param WorkflowMessage $workflowMessage
     * @return ProcessingMessage
     */
    protected function handleProcessData(WorkflowMessage $workflowMessage)
    {
        try {
            return $this->processData($workflowMessage);
        } catch (\Exception $ex) {
            ErrorHandler::stop();
            return LogMessage::logException($ex, $workflowMessage);
        }
    }

    /**
     * This method provides different modes depending on metadata configuration and matched files
     *
     * Mode 1: Metadata: fetch_mode = 'single_file'
     * @param WorkflowMessage $workflowMessage
     * @return \Prooph\Processing\Message\WorkflowMessage
     * @throws \InvalidArgumentException
     */
    private function collectData(WorkflowMessage $workflowMessage)
    {
        $metadata = $workflowMessage->metadata();

        if (isset($metadata[self::META_LOCATION]) && !isset($metadata[self::META_PATH])) {
            $metadata[self::META_PATH] = $this->locationTranslator->getPathFor($metadata[self::META_LOCATION]);
        }

        if (! isset($metadata[self::META_FILENAME_PATTERN])) throw new \InvalidArgumentException("Missing filename_pattern in metadata");
        if (! isset($metadata[self::META_FILE_TYPE])) throw new \InvalidArgumentException("Missing file_type in metadata");
        if (! isset($metadata[self::META_PATH])) throw new \InvalidArgumentException("Missing path in metadata");

        $metadata[self::META_PATH] = $this->sanitizePath($metadata[self::META_PATH]);

        if (! is_dir($metadata[self::META_PATH])) throw new \InvalidArgumentException(sprintf('Directory %s is invalid', $metadata[self::META_PATH]));
        if (! is_readable($metadata[self::META_PATH])) throw new \InvalidArgumentException(sprintf('Directory %s is not readable', $metadata[self::META_PATH]));


        /** @var $fileHandler FileTypeAdapter */
        $fileHandler = $this->fileTypeAdapters->get($metadata[self::META_FILE_TYPE]);

        ErrorHandler::start();

        $fileNames = array_map(
            function($filename) use ($metadata) {
                return $metadata[self::META_PATH] . DIRECTORY_SEPARATOR . $filename;
            },
            array_values(
                array_filter(
                    //We use scandir with descending order, so that files which include a date in the filename are sorted to newest first
                    scandir($metadata[self::META_PATH], 1),
                    function ($filename) use ($metadata) {
                        return (bool)preg_match($this->filterRegex($metadata[self::META_FILENAME_PATTERN]), $filename);
                    }
                )
            )
        );

        ErrorHandler::stop(true);

        $fetchMode = isset($metadata[self::META_FETCH_MODE])? $metadata[self::META_FETCH_MODE] : self::META_FETCH_MODE_SINGLE_FILE;

        if (count($fileNames)) {
            if ($fetchMode === self::META_FETCH_MODE_SINGLE_FILE) {
                return $this->collectDataFromSingleFile($fileNames[0], $workflowMessage, $fileHandler);
            } elseif ($fetchMode === self::META_FETCH_MODE_MULTI_FILES){
                return $this->collectDataFromMultipleFiles($fileNames, $workflowMessage, $fileHandler);
            } else {
                throw new \InvalidArgumentException("Metadata contains unknown fetch_mode");
            }
        } else {
            if ($fetchMode === self::META_FETCH_MODE_SINGLE_FILE) {
                throw new \InvalidArgumentException(sprintf("No file found for filename pattern %s", $metadata['filename_pattern']));
            } else {
                $typeClass = $workflowMessage->payload()->getTypeClass();

                if ($typeClass::prototype()->typeDescription()->nativeType() !== NativeType::COLLECTION) {
                    throw new \InvalidArgumentException(sprintf("Filename pattern %s matches no file and the requested processing type %s is not a collection.", $metadata['filename_pattern'], $typeClass));
                }

                $metadata[self::META_TOTAL_ITEMS] = 0;

                return $workflowMessage->answerWith($typeClass::fromNativeValue([]), $metadata);
            }
        }
    }

    private function collectDataFromSingleFile($filename, WorkflowMessage $workflowMessage, FileTypeAdapter $fileHandler)
    {
        $typeClass = $workflowMessage->payload()->getTypeClass();

        $metadata = $workflowMessage->metadata();

        $data = $fileHandler->readDataForType($filename, $typeClass::prototype(), $metadata);

        return $workflowMessage->answerWith($typeClass::fromNativeValue($data), $metadata);
    }

    private function collectDataFromMultipleFiles($fileNames, WorkflowMessage $workflowMessage, FileTypeAdapter $fileHandler)
    {
        $metadata = $workflowMessage->metadata();

        $fileDataType = (isset($metadata[self::META_FILE_DATA_TYPE]))? $metadata[self::META_FILE_DATA_TYPE] : $workflowMessage->payload()->getTypeClass();

        Assertion::implementsInterface($fileDataType, 'Prooph\Processing\Type\Type');

        $fileDataPrototype = $fileDataType::prototype();

        $fileDataCollection = [];
        $collectedData = null;
        $typeClass = $workflowMessage->payload()->getTypeClass();

        foreach ($fileNames as $filename) {
            $fileDataCollection[] = $fileHandler->readDataForType($filename, $fileDataPrototype, $metadata);
        }

        if (isset($metadata[self::META_MERGE_FILES]) && $metadata[self::META_MERGE_FILES]) {
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
                throw new \InvalidArgumentException(sprintf("Filename pattern %s matches multiple files but the requested processing type %s is not a collection.", $metadata['filename_pattern'], $typeClass));
            }

            $metadata[self::META_TOTAL_ITEMS] = count($fileDataCollection);

            $collectedData = $typeClass::fromNativeValue($fileDataCollection);
        }

        return $workflowMessage->answerWith($collectedData, $metadata);
    }

    /**
     * @param $regexp
     * @return string
     */
    private function filterRegex($regexp) {
        if (strpos($regexp, "/") !== false && mb_substr($regexp, -1) == "/") {
            return $regexp;
        }

        return "/" . preg_quote($regexp, "/") . "/";
    }

    /**
     * @param WorkflowMessage $workflowMessage
     * @throws \InvalidArgumentException
     */
    private function processData(WorkflowMessage $workflowMessage)
    {
        $metadata = $workflowMessage->metadata();

        if (isset($metadata[self::META_LOCATION]) && !isset($metadata[self::META_PATH])) {
            $metadata[self::META_PATH] = $this->locationTranslator->getPathFor($metadata[self::META_LOCATION]);
        }

        if (! isset($metadata[self::META_FILENAME_TEMPLATE])) throw new \InvalidArgumentException("Missing filename_pattern in metadata");
        if (! isset($metadata[self::META_FILE_TYPE])) throw new \InvalidArgumentException("Missing file_type in metadata");
        if (! isset($metadata[self::META_PATH])) throw new \InvalidArgumentException("Missing path in metadata");

        $metadata[self::META_PATH] = $this->sanitizePath($metadata[self::META_PATH]);

        if (! is_dir($metadata[self::META_PATH])) throw new \InvalidArgumentException(sprintf('Directory %s is invalid', $metadata[self::META_PATH]));
        if (! is_writable($metadata[self::META_PATH])) throw new \InvalidArgumentException(sprintf('Directory %s is not writable', $metadata[self::META_PATH]));

        $type = $workflowMessage->payload()->toType();
        $fileTypeAdapter = $this->fileTypeAdapters->get($metadata[self::META_FILE_TYPE]);

        if ($type->description()->nativeType() === NativeType::COLLECTION && isset($metadata[self::META_WRITE_MULTI_FILES]) && $metadata[self::META_WRITE_MULTI_FILES]) {
            foreach ($type as $index => $item) {
                $this->writeTypeToFile($item, $metadata, $fileTypeAdapter, $index);
            }
        } else {
            $this->writeTypeToFile($type, $metadata, $fileTypeAdapter);
        }

        return $workflowMessage->answerWithDataProcessingCompleted($metadata);
    }

    /**
     * @param $path
     * @return string
     */
    private function sanitizePath($path)
    {
        return rtrim(realpath($path), DIRECTORY_SEPARATOR);
    }

    /**
     * @param Type $type
     * @param array $metadata
     * @param FileTypeAdapter $fileTypeAdapter
     * @param null $itemIndex
     */
    private function writeTypeToFile(Type $type, array &$metadata, FileTypeAdapter $fileTypeAdapter, $itemIndex = null)
    {
        $filenameData = [self::FILENAME_DATA_METADATA => $metadata];

        if ($type->description()->nativeType() === NativeType::DICTIONARY) {
            $filenameData[self::FILENAME_DATA_DATA] = $this->dictionaryToArray($type);
        } elseif ($type instanceof SingleValue) {
            $filenameData[self::FILENAME_DATA_VALUE] = $type->value();
        }

        if (! is_null($itemIndex)) {
            $filenameData[self::FILENAME_DATA_ITEM_INDEX] = $itemIndex;
        }

        $filename = $this->fileNameRenderer->render($metadata[self::META_FILENAME_TEMPLATE], $filenameData);

        $fileTypeAdapter->writeDataOfType($metadata[self::META_PATH] . DIRECTORY_SEPARATOR . $filename, $type, $metadata);
    }

    /**
     * @param AbstractDictionary $dictionary
     * @return array
     */
    private function dictionaryToArray(AbstractDictionary $dictionary)
    {
        return json_decode(json_encode($dictionary), true);
    }
}
 
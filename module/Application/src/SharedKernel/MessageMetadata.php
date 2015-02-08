<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 2/1/15 - 9:48 PM
 */
namespace Application\SharedKernel;
use Prooph\Processing\Message\LogMessage;

/**
 * Class MessageMetadata
 *
 * Definition class for common metadata keys.
 * If these keys are present in the metadata of a workflow message or log message
 * they can be interpreted by the process monitor and/or by other connectors/plugins.
 *
 * @package Application\SharedKernel
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class MessageMetadata 
{
    /**
     * Specifies the start position of a chunk.
     * 0 defines the first item.
     */
    const OFFSET = 'offset';

    /**
     * Specifies the limit of a chunk.
     */
    const LIMIT = 'limit';

    /**
     * Count of all available items
     */
    const TOTAL_ITEMS = 'total_items';

    /**
     * Count of successful processed items
     */
    const SUCCESSFUL_ITEMS = LogMessage::MSG_PARAM_SUCCESSFUL_ITEMS;

    /**
     * Count of failed items
     */
    const FAILED_ITEMS = LogMessage::MSG_PARAM_FAILED_ITEMS;

    /**
     * Collection of failed messages per item
     */
    const FAILED_MESSAGES = LogMessage::MSG_PARAM_FAILED_MESSAGES;
} 
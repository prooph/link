<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/23/15 - 5:02 PM
 */
namespace Application\SharedKernel;

use Ginger\Message\MessageNameUtils;
use Ginger\Processor\Definition;

/**
 * Class ProcessToClientTranslator
 *
 * @package Application\SharedKernel
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class ProcessToClientTranslator 
{
    /**
     * @param $startMessage
     * @param array $processDefinition
     * @param array $knownGingerTypes
     * @param ScriptLocation $scriptLocation
     * @return array
     */
    public static function translate($startMessage, array $processDefinition, array $knownGingerTypes, ScriptLocation $scriptLocation)
    {
        $messageType = MessageNameUtils::getMessageSuffix($startMessage);

        foreach($processDefinition['tasks'] as $i => &$task) {
            $task['id'] = $i;
        }

        return [
            'id'  => $startMessage,
            'name' => $processDefinition['name'],
            'process_type' => $processDefinition['process_type'],
            'start_message' => [
                'message_type' => $messageType,
                'ginger_type' => GingerTypeClass::extractFromMessageName($startMessage, $knownGingerTypes)
            ],
            'tasks' => array_map(
                function ($task) use ($scriptLocation) {
                    if ($task['task_type'] === Definition::TASK_MANIPULATE_PAYLOAD) {
                        $task['manipulation_script'] = str_replace($scriptLocation->toString() . DIRECTORY_SEPARATOR, "", $task['manipulation_script']);
                    }

                    return $task;
                },
                $processDefinition['tasks']
            )
        ];
    }
} 
<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.01.15 - 22:57
 */

namespace ProcessorProxy\Api;

use Application\Service\AbstractRestController;
use Application\Service\ActionController;
use Assert\Assertion;
use Prooph\Processing\Message\WorkflowMessage;
use ProcessorProxy\Command\ForwardHttpMessage;
use ProcessorProxy\Model\MessageLogger;
use Prooph\ServiceBus\CommandBus;
use SystemConfig\Projection\ProcessingConfig;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\JsonModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

final class CollectDataTrigger extends AbstractRestController implements ActionController
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var MessageLogger
     */
    private $messageLogger;

    /**
     * @var ProcessingConfig
     */
    private $ProcessingConfig;

    public function __construct(MessageLogger $messageLogger, ProcessingConfig $config)
    {
        $this->messageLogger = $messageLogger;
        $this->ProcessingConfig  = $config;
    }

    public function create(array $data)
    {
        if (! array_key_exists("collect_data_trigger", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Root key collect_data_trigger missing in request data'));

        $data = $data["collect_data_trigger"];

        if (! array_key_exists('processing_type', $data)) return new ApiProblemResponse(new ApiProblem(422, 'Key processing_type is missing'));

        $processingType = $data['processing_type'];

        if (! class_exists($processingType)) return new ApiProblemResponse(new ApiProblem(422, 'Provided processing type is unknown'));

        try {
            Assertion::implementsInterface($processingType, 'Prooph\Processing\Type\Type');
        } catch (\InvalidArgumentException $ex) {
            return new ApiProblemResponse(new ApiProblem(422, 'Provided processing type is not valid'));
        }

        $wfMessage = WorkflowMessage::collectDataOf(
            $processingType::prototype(),
            __CLASS__,
            $this->ProcessingConfig->getNodeName()
        );

        $this->messageLogger->logIncomingMessage($wfMessage);

        $sbMessage = $wfMessage->toServiceBusMessage();

        $this->commandBus->dispatch(ForwardHttpMessage::createWith($sbMessage));

        /** @var $response Response */
        $response = $this->getResponse();

        $response->getHeaders()
            ->addHeaderLine(
                'Location',
                $this->url()->fromRoute('processor_proxy/api/messages', ['id' => $sbMessage->header()->uuid()->toString()])
            );

        $response->setStatusCode(201);

        return $response;
    }

    /**
     * @param CommandBus $commandBus
     * @return void
     */
    public function setCommandBus(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }
}
 
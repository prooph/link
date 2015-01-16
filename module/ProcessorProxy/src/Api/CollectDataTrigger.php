<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
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
use Ginger\Message\WorkflowMessage;
use ProcessorProxy\Command\ForwardHttpMessage;
use ProcessorProxy\Model\MessageLogger;
use Prooph\ServiceBus\CommandBus;
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

    public function __construct(MessageLogger $messageLogger)
    {
        $this->messageLogger = $messageLogger;
    }

    public function create(array $data)
    {
        if (! array_key_exists("collect_data_trigger", $data)) return new ApiProblemResponse(new ApiProblem(422, 'Root key collect_data_trigger missing in request data'));

        $data = $data["collect_data_trigger"];

        if (! array_key_exists('ginger_type', $data)) return new ApiProblemResponse(new ApiProblem(422, 'Key ginger_type is missing'));

        $gingerType = $data['ginger_type'];

        if (! class_exists($gingerType)) return new ApiProblemResponse(new ApiProblem(422, 'Provided ginger type is unknown'));

        try {
            Assertion::implementsInterface($gingerType, 'Ginger\Type\Type');
        } catch (\InvalidArgumentException $ex) {
            return new ApiProblemResponse(new ApiProblem(422, 'Provided ginger type is not valid'));
        }

        $wfMessage = WorkflowMessage::collectDataOf($gingerType::prototype());

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
 
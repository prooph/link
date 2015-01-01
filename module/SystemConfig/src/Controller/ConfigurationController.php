<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/8/14 - 5:31 PM
 */
namespace SystemConfig\Controller;

use Application\Service\AbstractActionController;
use Application\Service\TranslatorAwareController;
use Application\SharedKernel\ConfigLocation;
use Ginger\Processor\NodeName;
use SystemConfig\Command\ChangeNodeName;
use SystemConfig\Definition;
use Zend\Http\PhpEnvironment\Response;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

/**
 * Class ConfigurationController
 *
 * This controller respond only to AJAX requests. The UI triggers config changes individually.
 *
 * @package SystemConfig\Controller
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class ConfigurationController extends AbstractActionController
{
    /**
     * Handles a POST request that want to change the node name
     */
    public function changeNodeNameAction()
    {
        $nodeName = $this->bodyParam('node_name');

        if (! is_string($nodeName) || strlen($nodeName) < 3) {
            return new ApiProblemResponse(new ApiProblem(422, $this->translator->translate('The node name must be at least 3 characters long')));
        }

        $this->commandBus->dispatch(ChangeNodeName::to(
            NodeName::fromString($nodeName),
            ConfigLocation::fromPath(Definition::getSystemConfigDir())
        ));

        return ['success' => true];
    }
} 
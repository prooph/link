<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.12.14 - 21:01
 */

namespace SystemConfig\Controller;

use Prooph\ServiceBus\CommandBus;
use SystemConfig\Command\CreateDefaultGingerConfigFile;
use SystemConfig\Definition;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Class GingerSetUpController
 *
 * @package SystemConfig\Controller
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class GingerSetUpController extends AbstractActionController
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * Starts the initial set up of the Ginger system
     */
    public function startAction()
    {
        $this->commandBus->dispatch(CreateDefaultGingerConfigFile::in(Definition::SYSTEM_CONFIG_DIR));

        return $this->redirect()->toRoute('system_config');
    }
}
 
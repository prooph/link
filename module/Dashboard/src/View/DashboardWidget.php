<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 06.12.14 - 21:50
 */

namespace Dashboard\View;

use Assert\Assertion;
use Zend\View\Model\ViewModel;

/**
 * Class DashboardWidget
 *
 * @package Dashboard\View
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DashboardWidget extends ViewModel
{
    /**
     * @param string $template
     * @param string $title
     * @param int $requiredCols
     * @param null|array|\Traversable $variables
     *
     * @return \Dashboard\View\DashboardWidget
     */
    public static function initialize($template, $title, $requiredCols, $variables = null)
    {
        Assertion::string($template);
        Assertion::string($title);
        Assertion::integer($requiredCols);
        Assertion::min($requiredCols, 1);
        Assertion::max($requiredCols, 12);

        $options = [
            'required_cols' => $requiredCols,
            'title' => $title,
        ];

        $model = new self($variables, $options);

        $model->setTemplate($template);

        return $model;
    }

    /**
     * @return int
     */
    public function getRequiredCols()
    {
        return $this->getOption('required_cols');
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getOption('title');
    }
}
 
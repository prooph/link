<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 24.01.15 - 16:54
 */

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Class RiotTag
 *
 * @package Application\View\Helper
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class RiotTag extends AbstractHelper
{
    private $search = ['"', "\n"];

    private $replace = ['\"', ""];

    public function __invoke($tagName, $template = null, $jsFunction = null)
    {
        if (is_null($template)) {
            $template = $tagName;
            $tagName  = $this->getTagNameFromTemplate($template);
        }

        $this->assertTagName($tagName);
        $this->assertTemplate($template);

        $template = $this->getView()->partial($template);

        if (is_null($jsFunction)) {
            $jsFunction = $this->extractJsFunction($template);
            $template = $this->removeJsFromTemplate($template, $tagName);
        }

        return 'riot.tag("'.$tagName.'", "' . str_replace($this->search, $this->replace, $template) . '", '.$jsFunction.');';
    }

    private function getTagNameFromTemplate($template)
    {
        $this->assertTemplate($template);

        $parts = explode("/", $template);

        return array_pop($parts);
    }

    private function assertTagName($tagName)
    {
        if (!is_string($tagName)) {
            throw new \InvalidArgumentException("Riot tag name should be a string. got " . gettype($tagName));
        }
    }

    private function assertTemplate($template)
    {
        if (!is_string($template)) {
            throw new \InvalidArgumentException("Riot template should be a string. got " . gettype($template));
        }
    }

    private function extractJsFunction($template)
    {
        preg_match('/<script .*type="text\/javascript"[^>]*>[\s]*(?<func>function.+\});?[\s]*<\/script>/is', $template, $matches);

        if (! $matches['func']) {
            throw new \RuntimeException("Riot tag javascript function could not be found");
        }

        return $matches['func'];
    }

    private function removeJsFromTemplate($template, $tagName)
    {
        $template = preg_replace('/<script .*type="text\/javascript"[^>]*>.*<\/script>/is', "", $template);

        if (! $template) {
            throw new \RuntimeException("Riot tag template compilation failed for tag: " . $tagName . " with error code: " . preg_last_error());
        }

        return $template;
    }
}
 
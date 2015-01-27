<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 25.01.15 - 14:47
 */

namespace Application\Service;

use Assetic\Asset\BaseAsset;
use Assetic\Filter\FilterInterface;

/**
 * Class StringAsset
 *
 * Copied from assetic lib. AssetManager uses currently assetic 1.1.2 which has a broken StringAsset implementation
 * In newer versions the error is fixed but we have to wait until AssetManager updates the used assetic version.
 *
 * @package Application\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class StringAsset extends BaseAsset
{
    private $string;
    private $lastModified;

    /**
     * Constructor.
     *
     * @param string $content    The content of the asset
     * @param array  $filters    Filters for the asset
     * @param string $sourceRoot The source asset root directory
     * @param string $sourcePath The source asset path
     */
    public function __construct($content, $filters = array(), $sourceRoot = null, $sourcePath = null)
    {
        $this->string = $content;

        parent::__construct($filters, $sourceRoot, $sourcePath);
    }

    public function load(FilterInterface $additionalFilter = null)
    {
        $this->doLoad($this->string, $additionalFilter);
    }

    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
    }

    public function getLastModified()
    {
        return $this->lastModified;
    }
}
 
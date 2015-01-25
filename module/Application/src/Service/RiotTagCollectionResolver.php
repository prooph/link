<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 25.01.15 - 12:14
 */

namespace Application\Service;

use Application\View\Helper\RiotTag;
use Assetic\Asset\AssetCollection;
use AssetManager\Resolver\CollectionResolver;

final class RiotTagCollectionResolver extends CollectionResolver
{
    /**
     * @var RiotTag
     */
    private $riotTag;

    public function __construct(array $collections = array(), RiotTag $riotTag)
    {
        $this->setCollections($collections);
        $this->riotTag = $riotTag;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name)
    {
        if (!isset($this->collections[$name])) {
            return null;
        }

        if (!is_array($this->collections[$name])) {
            throw new \RuntimeException(
                "Collection with name $name is not an an array."
            );
        }

        $collection = new AssetCollection();
        $mimeType   = 'application/javascript';
        $collection->setTargetPath($name);

        foreach ($this->collections[$name] as $asset) {
            if (!is_string($asset)) {
                throw new \RuntimeException(
                    'Asset should be of type string. got ' . gettype($asset)
                );
            }

            if (null === ($content = $this->riotTag->__invoke($asset))) {
                throw new \RuntimeException("Riot tag '$asset' could not be found.");
            }

            $res = new StringAsset($content);


            $res->mimetype = $mimeType;

            $asset .= ".js";

            $this->getAssetFilterManager()->setFilters($asset, $res);

            $collection->add($res);
        }

        $collection->mimetype = $mimeType;

        return $collection;
    }
}
 
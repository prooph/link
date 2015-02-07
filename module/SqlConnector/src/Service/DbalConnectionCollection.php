<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 1/26/15 - 9:58 PM
 */
namespace SqlConnector\Service;

use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class DbalConnectionCollection
 *
 * @method DbalConnection get($key)
 *
 * @package SqlConnector\Service
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
final class DbalConnectionCollection extends ArrayCollection
{
    public static function fromConnectionConfigs(array $connectionConfigs)
    {
        return new self(
            array_map(
                function ($config) { return DbalConnection::fromConfiguration($config); },
                $connectionConfigs
            )
        );
    }
    /**
     * @param DbalConnection $connection
     * @return void
     */
    public function add($connection)
    {
        $this->set($connection->config()['dbname'], $connection);
    }

    /**
     * @param string $key
     * @param DbalConnection $connection
     */
    public function set($key, $connection)
    {
        Assertion::string($key, "Dbal connection key must be dbname");
        Assertion::isInstanceOf($connection, 'SqlConnector\Service\DbalConnection');

        parent::set($key, $connection);
    }

    /**
     * Only returns connection configs indexed by dbname
     *
     * @return array
     */
    public function toArray()
    {
        $connections = array();

        /** @var $connection DbalConnection */
        foreach ($this as $dbname => $connection)
        {
            $connections[$dbname] = $connection->config();
        }

        return $connections;
    }
} 
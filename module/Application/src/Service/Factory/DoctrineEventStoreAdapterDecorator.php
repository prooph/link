<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.01.15 - 23:26
 */

namespace Application\Service\Factory;

use Prooph\EventStore\Adapter\Doctrine\DoctrineEventStoreAdapter;

/**
 * Class DoctrineEventStoreAdapterDecorator
 *
 * @package Application\Service\Factory
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class DoctrineEventStoreAdapterDecorator extends DoctrineEventStoreAdapter
{
    /**
     * @param DoctrineEventStoreAdapter $adapter
     * @return \Doctrine\DBAL\Connection
     */
    public static function getConnectionOfAdapter(DoctrineEventStoreAdapter $adapter)
    {
        return $adapter->connection;
    }
}
 
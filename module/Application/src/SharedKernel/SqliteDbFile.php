<?php
/*
* This file is part of prooph/link.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 01.01.15 - 23:39
 */

namespace Application\SharedKernel;

use Zend\Stdlib\ErrorHandler;

/**
 * Class SqliteDbFile
 *
 * This class represents the sqlite db file used as default system and event store database
 *
 * @package Application\SharedKernel
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class SqliteDbFile
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $file;

    /**
     * @param string $fileName
     * @return SqliteDbFile
     */
    public static function fromFilename($fileName)
    {
        return new self($fileName);
    }

    /**
     * @param string $fileName
     * @return \Application\SharedKernel\SqliteDbFile
     */
    public static function initializeFromDist($fileName)
    {
        $dist = new self($fileName. '.dist');

        ErrorHandler::start();

        copy($dist->toString(), $fileName);

        ErrorHandler::stop(true);

        return new self($fileName);
    }

    /**
     * @param string $fileName
     * @throws \InvalidArgumentException
     */
    private function __construct($fileName)
    {
        if (! is_string($fileName)) throw new \InvalidArgumentException('File name must be a string');

        if (! file_exists($fileName)) throw new \InvalidArgumentException(sprintf('File %s does not exist', $fileName));

        $this->file = $fileName;
        $path = realpath($fileName);

        if (!is_writable($path)) throw new \InvalidArgumentException(sprintf('Sqlite db location %s must be writable', $path));
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->file;
    }
}
 
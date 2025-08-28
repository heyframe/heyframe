<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Processing\Reader;

use HeyFrame\Core\Content\ImportExport\Struct\Config;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
abstract class AbstractReader
{
    /**
     * @param resource $resource
     */
    abstract public function read(Config $config, $resource, int $offset): iterable;

    abstract public function getOffset(): int;

    protected function getDecorated(): AbstractReader
    {
        throw new \RuntimeException('Implement getDecorated');
    }
}

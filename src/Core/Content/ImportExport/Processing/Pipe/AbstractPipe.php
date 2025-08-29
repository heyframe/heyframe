<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Processing\Pipe;

use HeyFrame\Core\Content\ImportExport\Struct\Config;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('fundamentals@after-sales')]
abstract class AbstractPipe
{
    abstract public function in(Config $config, iterable $record): iterable;

    abstract public function out(Config $config, iterable $record): iterable;
}

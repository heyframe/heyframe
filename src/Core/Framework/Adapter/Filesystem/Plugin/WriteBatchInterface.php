<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Filesystem\Plugin;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
interface WriteBatchInterface
{
    public function writeBatch(CopyBatchInput ...$files): void;
}

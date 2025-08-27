<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Filesystem\Plugin;

interface WriteBatchInterface
{
    public function writeBatch(CopyBatchInput ...$files): void;
}

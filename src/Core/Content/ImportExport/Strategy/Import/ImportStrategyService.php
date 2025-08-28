<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Strategy\Import;

use HeyFrame\Core\Content\ImportExport\Struct\Config;
use HeyFrame\Core\Content\ImportExport\Struct\ImportResult;
use HeyFrame\Core\Content\ImportExport\Struct\Progress;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('fundamentals@after-sales')]
interface ImportStrategyService
{
    /**
     * @param array<string, mixed> $record
     * @param array<string, mixed> $row
     */
    public function import(
        array $record,
        array $row,
        Config $config,
        Progress $progress,
        Context $context,
    ): ImportResult;

    public function commit(Config $config, Progress $progress, Context $context): ImportResult;
}

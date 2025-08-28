<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Processing\Pipe;

use HeyFrame\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogEntity;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('fundamentals@after-sales')]
abstract class AbstractPipeFactory
{
    abstract public function create(ImportExportLogEntity $logEntity): AbstractPipe;

    abstract public function supports(ImportExportLogEntity $logEntity): bool;
}

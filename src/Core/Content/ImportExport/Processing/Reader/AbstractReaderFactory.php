<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Processing\Reader;

use HeyFrame\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
abstract class AbstractReaderFactory
{
    abstract public function create(ImportExportLogEntity $logEntity): AbstractReader;

    abstract public function supports(ImportExportLogEntity $logEntity): bool;
}

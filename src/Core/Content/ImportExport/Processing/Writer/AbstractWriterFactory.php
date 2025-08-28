<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Processing\Writer;

use HeyFrame\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
abstract class AbstractWriterFactory
{
    abstract public function create(ImportExportLogEntity $logEntity): AbstractWriter;

    abstract public function supports(ImportExportLogEntity $logEntity): bool;
}

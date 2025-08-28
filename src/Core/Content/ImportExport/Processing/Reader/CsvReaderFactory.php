<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Processing\Reader;

use HeyFrame\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
class CsvReaderFactory extends AbstractReaderFactory
{
    public function create(ImportExportLogEntity $logEntity): AbstractReader
    {
        return new CsvReader();
    }

    public function supports(ImportExportLogEntity $logEntity): bool
    {
        return $logEntity->getProfile()->getFileType() === 'text/csv';
    }
}

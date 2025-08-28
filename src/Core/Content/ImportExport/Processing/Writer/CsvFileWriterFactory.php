<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Processing\Writer;

use HeyFrame\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogEntity;
use HeyFrame\Core\Framework\Log\Package;
use League\Flysystem\FilesystemOperator;

#[Package('fundamentals@after-sales')]
class CsvFileWriterFactory extends AbstractWriterFactory
{
    /**
     * @internal
     */
    public function __construct(private readonly FilesystemOperator $filesystem)
    {
    }

    public function create(ImportExportLogEntity $logEntity): AbstractWriter
    {
        return new CsvFileWriter($this->filesystem);
    }

    public function supports(ImportExportLogEntity $logEntity): bool
    {
        return $logEntity->getProfile()?->getFileType() === 'text/csv';
    }
}

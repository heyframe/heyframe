<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\ScheduledTask;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

#[Package('fundamentals@after-sales')]
class CleanupImportExportFileTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'import_export_file.cleanup';
    }

    public static function getDefaultInterval(): int
    {
        return self::DAILY;
    }

    public static function shouldRescheduleOnFailure(): bool
    {
        return true;
    }
}

<?php

declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\ScheduledTask;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
final class DeleteThemeFilesTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'theme.delete_files';
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

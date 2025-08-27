<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Log\ScheduledTask;

use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class LogCleanupTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'log_entry.cleanup';
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

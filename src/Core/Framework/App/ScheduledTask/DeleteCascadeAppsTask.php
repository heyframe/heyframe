<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\ScheduledTask;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

#[Package('framework')]
class DeleteCascadeAppsTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'app_delete';
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

<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Version\Cleanup;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

#[Package('framework')]
class CleanupVersionTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'version.cleanup';
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

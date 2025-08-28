<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Context\Cleanup;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

#[Package('discovery')]
class CleanupChannelContextTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'channel_context.cleanup';
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

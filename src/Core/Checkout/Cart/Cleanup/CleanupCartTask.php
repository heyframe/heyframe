<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Cleanup;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

#[Package('checkout')]
class CleanupCartTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'cart.cleanup';
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

<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\InAppPurchase;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('checkout')]
final class InAppPurchaseUpdateTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'in-app-purchase.update';
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

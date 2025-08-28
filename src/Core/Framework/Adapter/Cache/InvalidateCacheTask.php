<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Cache;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\DeduplicatableMessageInterface;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

#[Package('framework')]
class InvalidateCacheTask extends ScheduledTask implements DeduplicatableMessageInterface
{
    public static function getTaskName(): string
    {
        return 'heyframe.invalidate_cache';
    }

    public static function getDefaultInterval(): int
    {
        // Run every five minutes
        return self::MINUTELY * 5;
    }

    public static function shouldRescheduleOnFailure(): bool
    {
        return true;
    }

    /**
     * @experimental stableVersion:v6.8.0 feature:DEDUPLICATABLE_MESSAGES
     */
    public function deduplicationId(): ?string
    {
        return 'invalidate-cache-task';
    }
}

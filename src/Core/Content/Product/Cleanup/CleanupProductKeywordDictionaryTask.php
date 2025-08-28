<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cleanup;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

#[Package('inventory')]
class CleanupProductKeywordDictionaryTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'product_keyword_dictionary.cleanup';
    }

    public static function getDefaultInterval(): int
    {
        return self::WEEKLY;
    }

    public static function shouldRescheduleOnFailure(): bool
    {
        return true;
    }
}

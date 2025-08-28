<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\ScheduledTask;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

/**
 * @internal
 */
#[Package('framework')]
class InstallServicesTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'services.install';
    }

    public static function getDefaultInterval(): int
    {
        return parent::DAILY;
    }

    public static function shouldRescheduleOnFailure(): bool
    {
        return true;
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\ScheduledTask;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Package('framework')]
class UpdateAppsTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'app_update';
    }

    public static function getDefaultInterval(): int
    {
        return self::DAILY;
    }

    public static function shouldRescheduleOnFailure(): bool
    {
        return true;
    }

    public static function shouldRun(ParameterBagInterface $bag): bool
    {
        return $bag->get('heyframe.deployment.runtime_extension_management');
    }
}

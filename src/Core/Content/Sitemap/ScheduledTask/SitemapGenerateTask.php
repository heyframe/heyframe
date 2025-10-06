<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\ScheduledTask;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Package('discovery')]
class SitemapGenerateTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'heyframe.sitemap_generate';
    }

    public static function getDefaultInterval(): int
    {
        return self::DAILY;
    }

    public static function shouldRun(ParameterBagInterface $bag): bool
    {
        return (bool) $bag->get('heyframe.sitemap.scheduled_task.enabled');
    }

    public static function shouldRescheduleOnFailure(): bool
    {
        return true;
    }
}

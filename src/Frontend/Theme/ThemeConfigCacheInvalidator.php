<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme;

use HeyFrame\Core\Framework\Adapter\Cache\CacheInvalidator;
use HeyFrame\Core\Framework\Adapter\Translation\Translator;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\Framework\Routing\CachedDomainLoader;
use HeyFrame\Frontend\Theme\Event\ThemeAssignedEvent;
use HeyFrame\Frontend\Theme\Event\ThemeConfigChangedEvent;
use HeyFrame\Frontend\Theme\Event\ThemeConfigResetEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('framework')]
class ThemeConfigCacheInvalidator implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly CacheInvalidator $cacheInvalidator,
    ) {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ThemeConfigChangedEvent::class => 'invalidate',
            ThemeAssignedEvent::class => 'assigned',
            ThemeConfigResetEvent::class => 'reset',
        ];
    }

    public function invalidate(ThemeConfigChangedEvent $event): void
    {
        $tags = [self::buildCacheTag($event->getThemeId())];

        $this->cacheInvalidator->invalidate($tags);
    }

    public function assigned(ThemeAssignedEvent $event): void
    {
        $channelId = $event->getChannelId();

        $this->cacheInvalidator->invalidate([
            self::buildCacheTag($event->getThemeId()),
            CachedDomainLoader::CACHE_KEY,
            Translator::tag($channelId),
        ]);
    }

    public function reset(ThemeConfigResetEvent $event): void
    {
        $this->cacheInvalidator->invalidate([self::buildCacheTag($event->getThemeId())]);
    }

    public static function buildCacheTag(string $themeId): string
    {
        return 'theme-config-' . $themeId;
    }
}

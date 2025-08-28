<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Cache\Telemetry;

use HeyFrame\Core\Framework\Adapter\Cache\InvalidateCacheEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Telemetry\Metrics\Meter;
use HeyFrame\Core\Framework\Telemetry\Metrics\Metric\ConfiguredMetric;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('framework')]
class CacheTelemetrySubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Meter $meter)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InvalidateCacheEvent::class => 'emitInvalidateCacheCountMetric',
        ];
    }

    public function emitInvalidateCacheCountMetric(): void
    {
        $this->meter->emit(new ConfiguredMetric('cache.invalidate.count', 1));
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Telemetry\Metrics\Config;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
readonly class TransportConfig
{
    /**
     * @param array<MetricConfig> $metricsConfig
     */
    public function __construct(public array $metricsConfig)
    {
    }
}

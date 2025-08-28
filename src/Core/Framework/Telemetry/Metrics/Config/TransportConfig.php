<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Telemetry\Metrics\Config;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
readonly class TransportConfig
{
    /**
     * @param array<MetricConfig> $metricsConfig
     */
    public function __construct(public array $metricsConfig)
    {
    }
}

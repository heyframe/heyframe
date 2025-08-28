<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Telemetry\Metrics\Config;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Telemetry\Metrics\Metric\Type;

/**
 * @internal
 *
 * @phpstan-import-type MetricDefinition from MetricConfig
 */
#[Package('framework')]
class TransportConfigProvider
{
    public function __construct(private readonly MetricConfigProvider $metricConfigProvider)
    {
    }

    public function getTransportConfig(): TransportConfig
    {
        return new TransportConfig(metricsConfig: $this->metricConfigProvider->all());
    }
}

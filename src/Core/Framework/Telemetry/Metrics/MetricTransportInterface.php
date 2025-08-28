<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Telemetry\Metrics;

use HeyFrame\Core\Framework\Telemetry\Metrics\Exception\MetricNotSupportedException;
use HeyFrame\Core\Framework\Telemetry\Metrics\Metric\Metric;

/**
 * @experimental feature:TELEMETRY_METRICS stableVersion:v6.8.0
 */
interface MetricTransportInterface
{
    /**
     * @throws MetricNotSupportedException
     */
    public function emit(Metric $metric): void;
}

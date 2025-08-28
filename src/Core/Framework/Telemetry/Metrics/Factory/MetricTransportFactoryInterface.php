<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Telemetry\Metrics\Factory;

use HeyFrame\Core\Framework\Telemetry\Metrics\Config\TransportConfig;
use HeyFrame\Core\Framework\Telemetry\Metrics\MetricTransportInterface;

/**
 * @experimental feature:TELEMETRY_METRICS stableVersion:v6.8.0
 */
interface MetricTransportFactoryInterface
{
    public function create(TransportConfig $transportConfig): MetricTransportInterface;
}

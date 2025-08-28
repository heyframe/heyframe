<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\Telemetry\Factory;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Telemetry\Metrics\Config\TransportConfig;
use HeyFrame\Core\Framework\Telemetry\Metrics\Factory\MetricTransportFactoryInterface;
use HeyFrame\Core\Framework\Telemetry\Metrics\MetricTransportInterface;
use HeyFrame\Core\Framework\Test\Telemetry\Transport\TraceableTransport;

/**
 * @internal
 */
#[Package('framework')]
class TraceableTransportFactory implements MetricTransportFactoryInterface
{
    public function create(TransportConfig $transportConfig): MetricTransportInterface
    {
        return new TraceableTransport();
    }
}

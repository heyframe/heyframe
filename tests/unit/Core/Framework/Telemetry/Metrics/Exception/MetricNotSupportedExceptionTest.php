<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\Telemetry\Metrics\Exception;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Telemetry\Metrics\Config\MetricConfig;
use HeyFrame\Core\Framework\Telemetry\Metrics\Exception\MetricNotSupportedException;
use HeyFrame\Core\Framework\Telemetry\Metrics\Metric\ConfiguredMetric;
use HeyFrame\Core\Framework\Telemetry\Metrics\Metric\Metric;
use HeyFrame\Core\Framework\Telemetry\Metrics\Metric\Type;
use HeyFrame\Core\Framework\Telemetry\Metrics\MetricTransportInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('framework')]
#[CoversClass(MetricNotSupportedException::class)]
class MetricNotSupportedExceptionTest extends TestCase
{
    public function testGetErrorCode(): void
    {
        $transport = $this->createMock(MetricTransportInterface::class);
        $metricConfig = new MetricConfig('test', description: 'test', type: Type::COUNTER, enabled: true, parameters: []);
        $metric = Metric::fromConfigured(new ConfiguredMetric('test', 1, []), $metricConfig);
        $exception = new MetricNotSupportedException($metric, $transport);
        static::assertSame('TELEMETRY__METRIC_NOT_SUPPORTED', $exception->getErrorCode());
    }
}

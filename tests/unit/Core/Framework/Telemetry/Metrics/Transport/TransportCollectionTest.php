<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\Telemetry\Metrics\Transport;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Telemetry\Metrics\Config\MetricConfig;
use HeyFrame\Core\Framework\Telemetry\Metrics\Config\TransportConfig;
use HeyFrame\Core\Framework\Telemetry\Metrics\Config\TransportConfigProvider;
use HeyFrame\Core\Framework\Telemetry\Metrics\Factory\MetricTransportFactoryInterface;
use HeyFrame\Core\Framework\Telemetry\Metrics\Metric\Type;
use HeyFrame\Core\Framework\Telemetry\Metrics\MetricTransportInterface;
use HeyFrame\Core\Framework\Telemetry\Metrics\Transport\TransportCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('framework')]
#[CoversClass(TransportCollection::class)]
class TransportCollectionTest extends TestCase
{
    public function testCreate(): void
    {
        $config = new TransportConfig(
            [MetricConfig::fromDefinition('test', ['type' => Type::GAUGE->value, 'description' => 'test', 'enabled' => true])]
        );

        $configProvider = $this->createMock(TransportConfigProvider::class);
        $configProvider->expects($this->once())
            ->method('getTransportConfig')
            ->willReturn($config);

        $transport1 = $this->createMock(MetricTransportInterface::class);
        $transport2 = $this->createMock(MetricTransportInterface::class);

        $factory1 = $this->createMock(MetricTransportFactoryInterface::class);
        $factory1->expects($this->once())
            ->method('create')
            ->with($config)
            ->willReturn($transport1);

        $factory2 = $this->createMock(MetricTransportFactoryInterface::class);
        $factory2->expects($this->once())
            ->method('create')
            ->with($config)
            ->willReturn($transport2);

        $factories = new \ArrayIterator([$factory1, $factory2]);

        $collection = TransportCollection::create($factories, $configProvider);

        $transports = iterator_to_array($collection->getIterator());
        static::assertCount(2, $transports);
        static::assertSame($transport1, $transports[0]);
        static::assertSame($transport2, $transports[1]);
    }
}

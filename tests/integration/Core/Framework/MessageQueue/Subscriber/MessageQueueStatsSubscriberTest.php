<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\MessageQueue\Subscriber;

use HeyFrame\Core\Framework\Increment\AbstractIncrementer;
use HeyFrame\Core\Framework\Increment\IncrementGatewayRegistry;
use HeyFrame\Core\Framework\Test\MessageQueue\fixtures\BarMessage;
use HeyFrame\Core\Framework\Test\MessageQueue\fixtures\FooMessage;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\QueueTestBehaviour;
use HeyFrame\Tests\Integration\Core\Framework\MessageQueue\fixtures\NoHandlerMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
class MessageQueueStatsSubscriberTest extends TestCase
{
    use IntegrationTestBehaviour;
    use QueueTestBehaviour;

    public function testListener(): void
    {
        /** @var AbstractIncrementer $pool */
        $pool = static::getContainer()
            ->get('heyframe.increment.gateway.registry')
            ->get(IncrementGatewayRegistry::MESSAGE_QUEUE_POOL);

        $pool->reset('message_queue_stats');

        /** @var MessageBusInterface $bus */
        $bus = static::getContainer()->get('messenger.bus.test_heyframe');

        $bus->dispatch(new FooMessage());
        $bus->dispatch(new BarMessage());
        $bus->dispatch(new BarMessage());
        $bus->dispatch(new BarMessage());

        $stats = $pool->list('message_queue_stats');
        static::assertSame(1, $stats[FooMessage::class]['count']);
        static::assertSame(3, $stats[BarMessage::class]['count']);

        $this->runWorker();

        $stats = $pool->list('message_queue_stats');
        static::assertSame(0, $stats[FooMessage::class]['count']);
        static::assertSame(0, $stats[BarMessage::class]['count']);

        $bus->dispatch(new NoHandlerMessage());

        $stats = $pool->list('message_queue_stats');
        static::assertSame(1, $stats[NoHandlerMessage::class]['count']);

        $this->runWorker();
        $stats = $pool->list('message_queue_stats');
        static::assertSame(0, $stats[NoHandlerMessage::class]['count']);
    }
}

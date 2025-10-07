<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Content\MailTemplate\Service\Event;

use HeyFrame\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use HeyFrame\Core\Content\Flow\Dispatching\StorableFlow;
use HeyFrame\Core\Content\Flow\Dispatching\Storer\ScalarValuesStorer;
use HeyFrame\Core\Content\MailTemplate\Service\Event\MailErrorEvent;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use Monolog\Level;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(MailErrorEvent::class)]
#[Package('after-sales')]
class MailErrorEventTest extends TestCase
{
    public function testScalarValuesCorrectly(): void
    {
        $event = new MailErrorEvent(
            Context::createDefaultContext()
        );

        $storer = new ScalarValuesStorer();

        $stored = $storer->store($event, []);

        $flow = new StorableFlow('foo', Context::createDefaultContext(), $stored);

        $storer->restore($flow);

        static::assertArrayHasKey('name', $flow->data());
        static::assertSame('mail.sent.error', $flow->getData('name'));
    }

    public function testInstantiate(): void
    {
        $exception = new \Exception('exception');
        $context = Context::createDefaultContext();

        $event = new MailErrorEvent(
            $context,
            Level::Error,
            $exception,
            'Test',
            '{{ subject }}',
            [
                'eventName' => CheckoutOrderPlacedEvent::EVENT_NAME,
                'shopName' => 'Frontend',
            ],
        );

        static::assertSame('Test', $event->getMessage());
        static::assertSame(Level::Error, $event->getLogLevel());
        static::assertSame([
            'exception' => (string) $exception,
            'message' => 'Test',
            'template' => '{{ subject }}',
            'eventName' => 'checkout.order.placed',
            'templateData' => [
                'eventName' => 'checkout.order.placed',
                'shopName' => 'Frontend',
            ],
        ], $event->getLogData());
        static::assertSame('mail.sent.error', $event->getName());
        static::assertSame($context, $event->getContext());
        static::assertSame($exception, $event->getThrowable());
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Profiling\Subscriber;

use HeyFrame\Core\Checkout\Cart\AbstractCartPersister;
use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Framework\Api\Context\SystemSource;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Routing\Event\ChannelContextResolvedEvent;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Profiling\Subscriber\CartDataCollectorSubscriber;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Currency\CurrencyEntity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(CartDataCollectorSubscriber::class)]
class CartDataCollectorSubscriberTest extends TestCase
{
    public function testEvents(): void
    {
        static::assertSame(
            [
                ChannelContextResolvedEvent::class => 'onContextResolved',
            ],
            CartDataCollectorSubscriber::getSubscribedEvents()
        );
    }

    public function testDataCollection(): void
    {
        $cartToken = Uuid::randomHex();

        $cart = new Cart('test-cart');
        $lineItem = new LineItem('line-item-id', 'product', 'product-id', 2);
        $lineItem->setLabel('Test Product');
        $lineItem->setPrice(new CalculatedPrice(
            100.00,
            200.00,
        ));

        $cart->addLineItems(new LineItemCollection([$lineItem]));
        $cart->setPrice(new CartPrice(
            200.00,
            200.00,
            200.00,
        ));

        $currency = new CurrencyEntity();
        $currency->setId(Uuid::randomHex());
        $currency->setIsoCode('CNY');

        $channelContext = $this->createMock(ChannelContext::class);
        $context = new Context(new SystemSource());
        $channelContext->method('getContext')->willReturn($context);
        $channelContext->method('getCurrency')->willReturn($currency);

        $event = new ChannelContextResolvedEvent($channelContext, $cartToken);

        $cartPersister = $this->createMock(AbstractCartPersister::class);
        $cartPersister->method('load')->willReturn($cart);

        $subscriber = new CartDataCollectorSubscriber($cartPersister);
        $subscriber->onContextResolved($event);
        $subscriber->collect(new Request(), new Response());

        static::assertEquals($cart, $subscriber->getCart());
        static::assertSame(1, $subscriber->getItemCount());
        static::assertSame(200.00, $subscriber->getCartTotal());
        static::assertSame('CNY', $subscriber->getCurrency());
    }

    public function testEmptyCart(): void
    {
        $cartToken = Uuid::randomHex();

        $cart = new Cart('empty-cart');

        $currency = new CurrencyEntity();
        $currency->setId(Uuid::randomHex());
        $currency->setIsoCode('CNY');

        $channelContext = $this->createMock(ChannelContext::class);
        $channelContext->method('getCurrency')->willReturn($currency);

        $event = new ChannelContextResolvedEvent($channelContext, $cartToken);
        $cartPersister = $this->createMock(AbstractCartPersister::class);
        $cartPersister->method('load')->willReturn($cart);

        $subscriber = new CartDataCollectorSubscriber($cartPersister);
        $subscriber->onContextResolved($event);
        $subscriber->collect(new Request(), new Response());
        static::assertEquals($cart, $subscriber->getCart());
        static::assertSame(0, $subscriber->getItemCount());
        static::assertSame(0.0, $subscriber->getCartTotal());
        static::assertSame('CNY', $subscriber->getCurrency());
    }

    public function testReset(): void
    {
        $cartToken = Uuid::randomHex();

        $cart = new Cart('test-cart');

        $currency = new CurrencyEntity();
        $currency->setId(Uuid::randomHex());
        $currency->setIsoCode('CNY');

        $channelContext = $this->createMock(ChannelContext::class);
        $channelContext->method('getCurrency')->willReturn($currency);

        $event = new ChannelContextResolvedEvent($channelContext, $cartToken);

        $cartPersister = $this->createMock(AbstractCartPersister::class);
        $cartPersister->method('load')->willReturn($cart);

        $subscriber = new CartDataCollectorSubscriber($cartPersister);
        $subscriber->onContextResolved($event);
        $subscriber->collect(new Request(), new Response());

        static::assertEquals($cart, $subscriber->getCart());

        $subscriber->reset();

        $subscriber->collect(new Request(), new Response());

        static::assertNull($subscriber->getCart());
    }
}

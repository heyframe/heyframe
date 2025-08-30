<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Cart;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use HeyFrame\Core\Checkout\Cart\AbstractCartPersister;
use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartBehavior;
use HeyFrame\Core\Checkout\Cart\CartCompressor;
use HeyFrame\Core\Checkout\Cart\CartPersister;
use HeyFrame\Core\Checkout\Cart\CartSerializationCleaner;
use HeyFrame\Core\Checkout\Cart\Event\CartSavedEvent;
use HeyFrame\Core\Checkout\Cart\Event\CartVerifyPersistEvent;
use HeyFrame\Core\Checkout\Cart\Exception\CartTokenNotFoundException;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\CheckoutPermissions;
use HeyFrame\Core\Content\Product\Cart\ProductNotFoundError;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\Test\Annotation\DisabledFeatures;
use HeyFrame\Core\Test\Generator;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @internal
 */
#[Package('checkout')]
#[CoversClass(CartPersister::class)]
class CartPersisterTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testLoadWithNotExistingToken(): void
    {
        $connection = $this->createMock(Connection::class);
        $cartSerializationCleaner = $this->createMock(CartSerializationCleaner::class);
        $eventDispatcher = new EventDispatcher();
        $connection->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(false);

        $persister = new CartPersister($connection, $eventDispatcher, $cartSerializationCleaner, new CartCompressor(false, 'gzip'));

        $e = null;

        try {
            $persister->load('not_existing_token', Generator::generateChannelContext());
        } catch (\Exception $e) {
        }

        static::assertInstanceOf(CartTokenNotFoundException::class, $e);
        static::assertSame('not_existing_token', $e->getParameter('token'));
    }

    public function testLoadWithExistingToken(): void
    {
        $connection = $this->createMock(Connection::class);
        $cartSerializationCleaner = $this->createMock(CartSerializationCleaner::class);
        $eventDispatcher = new EventDispatcher();
        $connection->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(
                ['payload' => serialize(new Cart('existing')), 'rule_ids' => json_encode([]), 'compressed' => 0]
            );

        $persister = new CartPersister($connection, $eventDispatcher, $cartSerializationCleaner, new CartCompressor(false, 'gzip'));
        $cart = $persister->load('existing', Generator::generateChannelContext());

        static::assertEquals(new Cart('existing'), $cart);
    }

    public function testEmptyCartShouldNotBeSaved(): void
    {
        $connection = $this->createMock(Connection::class);
        $cartSerializationCleaner = $this->createMock(CartSerializationCleaner::class);

        $eventDispatcher = new EventDispatcher();

        // Cart should be deleted (in case it exists).
        // Cart should not be inserted or updated.
        $this->expectSqlQuery($connection, 'DELETE FROM `cart`');

        $persister = new CartPersister($connection, $eventDispatcher, $cartSerializationCleaner, new CartCompressor(false, 'gzip'));

        $cart = new Cart('existing');

        $persister->save($cart, Generator::generateChannelContext());
    }

    public function testEmptyCartWithCustomerCommentIsSaved(): void
    {
        $cart = new Cart('existing');
        $cart->setCustomerComment('Foo');

        static::getContainer()->get(CartPersister::class)
            ->save($cart, $this->getChannelContext($cart->getToken()));

        $token = static::getContainer()->get(Connection::class)
            ->fetchOne('SELECT token FROM cart WHERE token = :token', ['token' => $cart->getToken()]);

        static::assertNotEmpty($token);
    }

    public function testSaveWithItems(): void
    {
        $cart = new Cart('existing');
        $cart->add(
            (new LineItem('A', 'test'))
                ->setPrice(new CalculatedPrice(0, 0))
                ->setLabel('test')
        );

        static::getContainer()->get(CartPersister::class)
            ->save($cart, $this->getChannelContext($cart->getToken()));

        $token = static::getContainer()->get(Connection::class)
            ->fetchOne('SELECT token FROM cart WHERE token = :token', ['token' => $cart->getToken()]);

        static::assertNotEmpty($token);
    }

    /**
     * @deprecated tag:v6.8.0 - Will be removed
     */
    #[DisabledFeatures(['v6.8.0.0'])]
    public function testRecalculationCartShouldNotBeSaved(): void
    {
        $cartBehavior = new CartBehavior([], true, true);

        $cart = new Cart('existing');
        $cart->setBehavior($cartBehavior);
        $cart->add(
            (new LineItem('A', 'test'))
                ->setPrice(new CalculatedPrice(0, 0))
                ->setLabel('test')
        );

        static::getContainer()->get(CartPersister::class)
            ->save($cart, $this->getChannelContext($cart->getToken()));

        $token = static::getContainer()->get(Connection::class)
            ->fetchOne('SELECT token FROM cart WHERE token = :token', ['token' => $cart->getToken()]);

        static::assertFalse($token);
    }

    public function testSkipPersistenceCartShouldNotBeSaved(): void
    {
        $cartBehavior = new CartBehavior([CheckoutPermissions::SKIP_CART_PERSISTENCE => true], true);

        $cart = new Cart('existing');
        $cart->setBehavior($cartBehavior);
        $cart->add(
            (new LineItem('A', 'test'))
                ->setPrice(new CalculatedPrice(0, 0))
                ->setLabel('test')
        );

        static::getContainer()->get(CartPersister::class)
            ->save($cart, $this->getChannelContext($cart->getToken()));

        $token = static::getContainer()->get(Connection::class)
            ->fetchOne('SELECT token FROM cart WHERE token = :token', ['token' => $cart->getToken()]);

        static::assertFalse($token);
    }

    public function testCartSavedEventIsFired(): void
    {
        $eventDispatcher = static::getContainer()->get('event_dispatcher');

        $caughtEvent = null;
        $this->addEventListener($eventDispatcher, CartSavedEvent::class, static function (CartSavedEvent $event) use (&$caughtEvent): void {
            $caughtEvent = $event;
        });

        $cart = new Cart('existing');
        $cart->add(
            (new LineItem('A', 'test'))
                ->setPrice(new CalculatedPrice(0, 0))
                ->setLabel('test')
        );

        static::getContainer()->get(CartPersister::class)
            ->save($cart, $this->getChannelContext($cart->getToken()));

        $token = static::getContainer()->get(Connection::class)
            ->fetchOne('SELECT token FROM cart WHERE token = :token', ['token' => $cart->getToken()]);

        static::assertNotEmpty($token);

        static::assertInstanceOf(CartSavedEvent::class, $caughtEvent);
        static::assertCount(1, $caughtEvent->getCart()->getLineItems());
        $firstLineItem = $caughtEvent->getCart()->getLineItems()->first();
        static::assertInstanceOf(LineItem::class, $firstLineItem);
        static::assertSame('test', $firstLineItem->getLabel());
    }

    public function testCartCanBeUnserialized(): void
    {
        $cart = unserialize((string) file_get_contents(__DIR__ . '/fixtures/cart.blob'));

        static::assertInstanceOf(Cart::class, $cart);
    }

    public function testCartVerifyPersistEventIsFiredAndNotPersisted(): void
    {
        $connection = $this->createMock(Connection::class);
        $cartSerializationCleaner = $this->createMock(CartSerializationCleaner::class);
        $eventDispatcher = new EventDispatcher();

        $this->expectSqlQuery($connection, 'DELETE FROM `cart`');

        $caughtEvent = null;
        $this->addEventListener($eventDispatcher, CartVerifyPersistEvent::class, function (CartVerifyPersistEvent $event) use (&$caughtEvent): void {
            $caughtEvent = $event;
        });

        $persister = new CartPersister($connection, $eventDispatcher, $cartSerializationCleaner, new CartCompressor(false, 'gzip'));

        $cart = new Cart('existing');

        $persister->save(
            $cart,
            $this->getChannelContext($cart->getToken())
        );
        static::assertInstanceOf(CartVerifyPersistEvent::class, $caughtEvent, CartVerifyPersistEvent::class . ' did not run');
        static::assertFalse($caughtEvent->shouldBePersisted());
        static::assertCount(0, $caughtEvent->getCart()->getLineItems());
    }

    public function testCartVerifyPersistEventIsFiredAndPersisted(): void
    {
        $caughtEvent = null;
        $this->addEventListener(static::getContainer()->get('event_dispatcher'), CartVerifyPersistEvent::class, static function (CartVerifyPersistEvent $event) use (&$caughtEvent): void {
            $caughtEvent = $event;
        });

        $cart = new Cart('existing');
        $cart->addLineItems(new LineItemCollection([
            new LineItem(Uuid::randomHex(), LineItem::PROMOTION_LINE_ITEM_TYPE, Uuid::randomHex(), 1),
        ]));

        static::getContainer()->get(CartPersister::class)
            ->save($cart, $this->getChannelContext($cart->getToken()));

        $token = static::getContainer()->get(Connection::class)
            ->fetchOne('SELECT token FROM cart WHERE token = :token', ['token' => $cart->getToken()]);

        static::assertNotEmpty($token);

        static::assertInstanceOf(CartVerifyPersistEvent::class, $caughtEvent);
        static::assertTrue($caughtEvent->shouldBePersisted());
        static::assertCount(1, $caughtEvent->getCart()->getLineItems());
    }

    public function testCartVerifyPersistEventIsFiredAndModified(): void
    {
        $caughtEvent = null;
        $this->addEventListener(static::getContainer()->get('event_dispatcher'), CartVerifyPersistEvent::class, static function (CartVerifyPersistEvent $event) use (&$caughtEvent): void {
            $caughtEvent = $event;
            $event->setShouldPersist(false);
        });

        $cart = new Cart('existing');
        $cart->addLineItems(new LineItemCollection([
            new LineItem(Uuid::randomHex(), LineItem::PROMOTION_LINE_ITEM_TYPE, Uuid::randomHex(), 1),
        ]));

        static::getContainer()->get(CartPersister::class)
            ->save($cart, $this->getChannelContext($cart->getToken()));

        $token = static::getContainer()->get(Connection::class)
            ->fetchOne('SELECT token FROM cart WHERE token = :token', ['token' => $cart->getToken()]);

        static::assertEmpty($token);

        static::assertInstanceOf(CartVerifyPersistEvent::class, $caughtEvent);
        static::assertFalse($caughtEvent->shouldBePersisted());
        static::assertCount(1, $caughtEvent->getCart()->getLineItems());
    }

    public function testPrune(): void
    {
        static::getContainer()->get(Connection::class)->executeStatement('DELETE FROM cart');

        $ids = new IdsCollection();

        $now = new \DateTimeImmutable();

        $this->createCart($ids->create('cart-1'), $now);

        $expiredDate1 = $now->modify(\sprintf('-%d day', 121));
        $this->createCart($ids->create('cart-2'), $expiredDate1);

        $this->createCart($ids->create('cart-3'), $expiredDate1, $now);

        $expiredDate2 = $now->modify(\sprintf('-%d day', 122));
        $this->createCart($ids->create('cart-4'), $expiredDate2, $expiredDate1);

        static::getContainer()->get(CartPersister::class)->prune(30);

        $carts = static::getContainer()->get(Connection::class)
            ->fetchFirstColumn('SELECT token FROM cart');

        static::assertCount(2, $carts);
        static::assertContains($ids->get('cart-1'), $carts);
        static::assertContains($ids->get('cart-3'), $carts);
    }

    public function testSaveCartWithoutErrorCleanup(): void
    {
        $cart = new Cart('existing');
        $cart->add(
            (new LineItem('A', 'test'))
                ->setPrice(new CalculatedPrice(0, 0))
                ->setLabel('test')
        );

        $cart->addErrors(new ProductNotFoundError(Uuid::randomHex()));

        $context = $this->getChannelContext($cart->getToken());
        $cartPersister = static::getContainer()->get(CartPersister::class);
        $cartPersister->save($cart, $context);

        $cart = $cartPersister->load($cart->getToken(), $context);

        static::assertNotEmpty($cart->getLineItems());
        static::assertEmpty($cart->getErrors());
    }

    public function testSaveCartWithPersistCartErrorPermission(): void
    {
        $cart = new Cart('existing');
        $cart->add(
            (new LineItem('A', 'test'))
                ->setPrice(new CalculatedPrice(0, 0))
                ->setLabel('test')
        );

        $cart->setBehavior(new CartBehavior([
            AbstractCartPersister::PERSIST_CART_ERROR_PERMISSION => true,
        ]));

        $productId = Uuid::randomHex();
        $cart->addErrors(new ProductNotFoundError($productId));

        $context = $this->getChannelContext($cart->getToken());
        $cartPersister = static::getContainer()->get(CartPersister::class);
        $cartPersister->save($cart, $context);

        $cart = $cartPersister->load($cart->getToken(), $context);

        static::assertNotEmpty($cart->getLineItems());
        static::assertNotEmpty($cart->getErrors());

        $error = $cart->getErrors()->first();
        static::assertInstanceOf(ProductNotFoundError::class, $error);
        static::assertEquals(['id' => $productId], $error->getParameters());
    }

    private function getChannelContext(string $token): ChannelContext
    {
        return static::getContainer()
            ->get(ChannelContextFactory::class)
            ->create($token, TestDefaults::CHANNEL);
    }

    private function expectSqlQuery(MockObject $connection, string $beginOfSql): void
    {
        $connection->expects($this->once())
            ->method('prepare')
            ->with(
                static::callback(fn (string $sql): bool => \str_starts_with(\trim($sql), $beginOfSql))
            )
            ->willReturnCallback(fn (string $sql): Statement => static::getContainer()->get(Connection::class)->prepare($sql));
    }

    private function createCart(string $token, \DateTimeImmutable $date, ?\DateTimeImmutable $updatedAt = null): void
    {
        $cart = [
            'token' => $token,
            'payload' => '',
            'rule_ids' => json_encode([]),
            'created_at' => $updatedAt?->format(Defaults::STORAGE_DATE_TIME_FORMAT) ?? $date->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        static::getContainer()->get(Connection::class)
            ->insert('cart', $cart);
    }
}

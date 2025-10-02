<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Cart\Channel;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Cart\Channel\CartService;
use HeyFrame\Core\Checkout\Cart\Event\AfterLineItemAddedEvent;
use HeyFrame\Core\Checkout\Cart\Event\AfterLineItemQuantityChangedEvent;
use HeyFrame\Core\Checkout\Cart\Event\AfterLineItemRemovedEvent;
use HeyFrame\Core\Checkout\Cart\Event\BeforeLineItemAddedEvent;
use HeyFrame\Core\Checkout\Cart\Event\BeforeLineItemQuantityChangedEvent;
use HeyFrame\Core\Checkout\Cart\Event\BeforeLineItemRemovedEvent;
use HeyFrame\Core\Checkout\Cart\Event\CartCreatedEvent;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItemFactoryHandler\ProductLineItemFactory;
use HeyFrame\Core\Checkout\Cart\PriceDefinitionFactory;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\CountryAddToChannelTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\MailTemplateTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseHelper\CallableClass;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
class CartServiceTest extends TestCase
{
    use CountryAddToChannelTestBehaviour;
    use IntegrationTestBehaviour;
    use MailTemplateTestBehaviour;

    /**
     * @var EntityRepository<CustomerCollection>
     */
    private EntityRepository $customerRepository;

    private Connection $connection;

    private string $productId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = static::getContainer()->get(Connection::class);
        $this->customerRepository = static::getContainer()->get('customer.repository');

        $context = Context::createDefaultContext();
        $this->productId = Uuid::randomHex();
        $product = [
            'id' => $this->productId,
            'productNumber' => $this->productId,
            'name' => 'test',
            'stock' => 10,
            'price' => [
                ['currencyId' => Defaults::CURRENCY, 'gross' => 100],
            ],
            'active' => true,
            'visibilities' => [
                ['channelId' => TestDefaults::CHANNEL, 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
            ],
        ];

        static::getContainer()->get('product.repository')
            ->create([$product], $context);
    }

    public function testCreateNewWithEvent(): void
    {
        $caughtEvent = null;
        $this->addEventListener(static::getContainer()->get('event_dispatcher'), CartCreatedEvent::class, static function (CartCreatedEvent $event) use (&$caughtEvent): void {
            $caughtEvent = $event;
        });

        $cartService = static::getContainer()->get(CartService::class);

        $token = Uuid::randomHex();
        $newCart = $cartService->createNew($token);

        static::assertInstanceOf(CartCreatedEvent::class, $caughtEvent);
        static::assertSame($newCart, $caughtEvent->getCart());
        static::assertSame($newCart, $cartService->getCart($token, $this->getChannelContext()));
        static::assertNotSame($newCart, $cartService->createNew($token));
    }

    public function testLineItemAddedEventFired(): void
    {
        $dispatcher = static::getContainer()->get('event_dispatcher');

        $isMerged = null;
        $this->addEventListener($dispatcher, BeforeLineItemAddedEvent::class, static function (BeforeLineItemAddedEvent $addedEvent) use (&$isMerged): void {
            $isMerged = $addedEvent->isMerged();
        });

        $cartService = static::getContainer()->get(CartService::class);

        $context = $this->getChannelContext();

        $cartId = Uuid::randomHex();
        $cart = $cartService->getCart($cartId, $context);
        $cartService->add(
            $cart,
            (new LineItem('test', 'test'))->setStackable(true),
            $context
        );

        static::assertNotNull($isMerged);
        static::assertFalse($isMerged);

        $cartService->add(
            $cart,
            new LineItem('test', 'test'),
            $context
        );

        /** @phpstan-ignore staticMethod.impossibleType ($isMerged modified by listener) */
        static::assertTrue($isMerged);
    }

    public function testAfterLineItemAddedEventFired(): void
    {
        $dispatcher = static::getContainer()->get('event_dispatcher');

        $listener = $this->getMockBuilder(CallableClass::class)->getMock();
        $listener->expects($this->once())->method('__invoke');

        $this->addEventListener($dispatcher, AfterLineItemAddedEvent::class, $listener);

        $cartService = static::getContainer()->get(CartService::class);

        $context = $this->getChannelContext();

        $cartId = Uuid::randomHex();
        $cart = $cartService->getCart($cartId, $context);
        $cartService->add(
            $cart,
            new LineItem('test', 'test'),
            $context
        );
    }

    public function testLineItemRemovedEventFired(): void
    {
        $dispatcher = static::getContainer()->get('event_dispatcher');

        $listener = $this->getMockBuilder(CallableClass::class)->getMock();
        $listener->expects($this->once())->method('__invoke');

        $this->addEventListener($dispatcher, BeforeLineItemRemovedEvent::class, $listener);

        $cartService = static::getContainer()->get(CartService::class);

        $context = $this->getChannelContext();

        $lineItem = (new ProductLineItemFactory(new PriceDefinitionFactory()))->create(['id' => $this->productId, 'referencedId' => $this->productId], $context);

        $cart = $cartService->getCart($context->getToken(), $context);

        $cart = $cartService->add($cart, $lineItem, $context);

        static::assertTrue($cart->has($this->productId));

        $cart = $cartService->remove($cart, $this->productId, $context);

        static::assertFalse($cart->has($this->productId));
    }

    public function testAfterLineItemRemovedEventFired(): void
    {
        $dispatcher = static::getContainer()->get('event_dispatcher');

        $listener = $this->getMockBuilder(CallableClass::class)->getMock();
        $listener->expects($this->once())->method('__invoke');

        $this->addEventListener($dispatcher, AfterLineItemRemovedEvent::class, $listener);

        $cartService = static::getContainer()->get(CartService::class);

        $context = $this->getChannelContext();

        $lineItem = (new ProductLineItemFactory(new PriceDefinitionFactory()))->create(['id' => $this->productId, 'referencedId' => $this->productId], $context);

        $cart = $cartService->getCart($context->getToken(), $context);

        $cart = $cartService->add($cart, $lineItem, $context);

        static::assertTrue($cart->has($this->productId));

        $cart = $cartService->remove($cart, $this->productId, $context);

        static::assertFalse($cart->has($this->productId));
    }

    public function testLineItemQuantityChangedEventFired(): void
    {
        $dispatcher = static::getContainer()->get('event_dispatcher');

        $listener = $this->getMockBuilder(CallableClass::class)->getMock();
        $listener->expects($this->once())->method('__invoke');

        $this->addEventListener($dispatcher, BeforeLineItemQuantityChangedEvent::class, $listener);

        $cartService = static::getContainer()->get(CartService::class);

        $context = $this->getChannelContext();

        $lineItem = (new ProductLineItemFactory(new PriceDefinitionFactory()))->create(['id' => $this->productId, 'referencedId' => $this->productId], $context);

        $cart = $cartService->getCart($context->getToken(), $context);

        $cart = $cartService->add($cart, $lineItem, $context);

        static::assertTrue($cart->has($this->productId));

        $cartService->changeQuantity($cart, $this->productId, 100, $context);
    }

    public function testAfterLineItemQuantityChangedEventFired(): void
    {
        $dispatcher = static::getContainer()->get('event_dispatcher');

        $listener = $this->getMockBuilder(CallableClass::class)->getMock();
        $listener->expects($this->once())->method('__invoke');

        $this->addEventListener($dispatcher, AfterLineItemQuantityChangedEvent::class, $listener);

        $cartService = static::getContainer()->get(CartService::class);

        $context = $this->getChannelContext();

        $lineItem = (new ProductLineItemFactory(new PriceDefinitionFactory()))->create(['id' => $this->productId, 'referencedId' => $this->productId], $context);

        $cart = $cartService->getCart($context->getToken(), $context);

        $cart = $cartService->add($cart, $lineItem, $context);

        static::assertTrue($cart->has($this->productId));

        $cartService->changeQuantity($cart, $this->productId, 100, $context);
    }

    public function testLineItemAddAndUpdate(): void
    {
        $cartService = static::getContainer()->get(CartService::class);

        $context = $this->getChannelContext();

        $productId = Uuid::randomHex();
        $product = [
            'id' => $productId,
            'productNumber' => $productId,
            'name' => 'test',
            'stock' => 10,
            'price' => [
                ['currencyId' => Defaults::CURRENCY, 'gross' => 5],
            ],
            'active' => true,
            'visibilities' => [
                ['channelId' => TestDefaults::CHANNEL, 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
            ],
        ];

        static::getContainer()->get('product.repository')
            ->create([$product], $context->getContext());

        $lineItem = (new ProductLineItemFactory(new PriceDefinitionFactory()))->create(['id' => $productId, 'referencedId' => $productId], $context);
        $cart = $cartService->getCart($context->getToken(), $context);
        $cart = $cartService->add($cart, $lineItem, $context);

        $lineItem = $cart->getLineItems()->get($productId);

        static::assertInstanceOf(LineItem::class, $lineItem);
        static::assertSame(1, $lineItem->getQuantity());
        static::assertTrue($lineItem->isStackable());
        static::assertTrue($lineItem->isRemovable());

        $cart = $cartService->update($cart, ['foo' => [
            'id' => $productId,
            'quantity' => 20,
            'payload' => ['foo' => 'bar'],
            'stackable' => false,
            'removable' => false,
        ]], $context);

        static::assertSame(20, $lineItem->getQuantity());
        static::assertTrue($lineItem->isStackable());
        static::assertTrue($lineItem->isRemovable());
        static::assertSame('bar', $lineItem->getPayloadValue('foo'));
    }

    public function testRemoveLineItems(): void
    {
        $cartService = static::getContainer()->get(CartService::class);

        $context = $this->getChannelContext();

        $productId1 = Uuid::randomHex();
        $productId2 = Uuid::randomHex();
        $productId3 = Uuid::randomHex();

        $products = [];
        foreach ([$productId1, $productId2, $productId3] as $productId) {
            $products[] = [
                'id' => $productId,
                'productNumber' => $productId,
                'name' => 'test',
                'stock' => 10,
                'price' => [
                    ['currencyId' => Defaults::CURRENCY, 'gross' => 5, 'net' => 5, 'linked' => false],
                ],
                'tax' => ['id' => Uuid::randomHex(), 'name' => 'test', 'taxRate' => 18],
                'manufacturer' => ['name' => 'test'],
                'active' => true,
                'visibilities' => [
                    ['channelId' => TestDefaults::CHANNEL, 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
                ],
            ];
        }

        static::getContainer()->get('product.repository')
            ->create($products, $context->getContext());

        $lineItems = [];
        foreach ($products as $product) {
            $lineItems[] = (new ProductLineItemFactory(new PriceDefinitionFactory()))->create(['id' => $product['id'], 'referencedId' => $product['id']], $context);
        }

        $cart = $cartService->getCart($context->getToken(), $context);
        $cart = $cartService->add($cart, $lineItems, $context);

        static::assertCount(3, $cart->getLineItems());

        $cart = $cartService->removeItems($cart, [
            $productId1,
            $productId2,
        ], $context);

        static::assertCount(1, $cart->getLineItems());

        $remainingLineItem = $cart->getLineItems()->get($productId3);
        static::assertInstanceOf(LineItem::class, $remainingLineItem);
        static::assertSame($productId3, $remainingLineItem->getReferencedId());
    }

    public function testZeroPricedItemsCanBeAddedToCart(): void
    {
        $cartService = static::getContainer()->get(CartService::class);

        $context = $this->getChannelContext();

        $productId = Uuid::randomHex();
        $product = [
            'id' => $productId,
            'productNumber' => $productId,
            'name' => 'test',
            'stock' => 10,
            'price' => [
                ['currencyId' => Defaults::CURRENCY, 'gross' => 0, 'net' => 0, 'linked' => false],
            ],
            'tax' => ['id' => Uuid::randomHex(), 'name' => 'test', 'taxRate' => 18],
            'manufacturer' => ['name' => 'test'],
            'active' => true,
            'visibilities' => [
                ['channelId' => TestDefaults::CHANNEL, 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
            ],
        ];

        static::getContainer()->get('product.repository')
            ->create([$product], $context->getContext());

        $lineItem = (new ProductLineItemFactory(new PriceDefinitionFactory()))->create(['id' => $productId, 'referencedId' => $productId], $context);

        $cart = $cartService->getCart($context->getToken(), $context);

        $cart = $cartService->add($cart, $lineItem, $context);

        static::assertTrue($cart->has($productId));
        static::assertSame(0.0, $cart->getPrice()->getTotalPrice());

        $calculatedLineItem = $cart->getLineItems()->get($productId);
        static::assertNotNull($calculatedLineItem);
        static::assertNotNull($calculatedLineItem->getPrice());
        static::assertSame(0.0, $calculatedLineItem->getPrice()->getTotalPrice());
    }

    public function testCartCreatedWithGivenToken(): void
    {
        $channelContextFactory = static::getContainer()->get(ChannelContextFactory::class);
        $context = $channelContextFactory->create(Uuid::randomHex(), TestDefaults::CHANNEL);

        $token = Uuid::randomHex();
        $cartService = static::getContainer()->get(CartService::class);
        $cart = $cartService->getCart($token, $context);

        static::assertSame($token, $cart->getToken());
    }

    private function createCustomer(string $addressId, string $mail, string $password, Context $context): void
    {
        $this->connection->executeStatement('DELETE FROM customer WHERE email = :mail', [
            'mail' => $mail,
        ]);

        $customer = [
            'channelId' => TestDefaults::CHANNEL,
            'defaultBillingAddressId' => $addressId,
            'groupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'email' => $mail,
            'password' => $password,
            'nickname' => 'match',
            'customerNumber' => 'not',
        ];

        $this->customerRepository->create([$customer], $context);
    }

    private function getChannelContext(): ChannelContext
    {
        $this->addCountriesToChannel();

        return static::getContainer()->get(ChannelContextFactory::class)
            ->create(Uuid::randomHex(), TestDefaults::CHANNEL);
    }

    private function setDomainForChannel(string $domain, string $languageId, Context $context): void
    {
        /** @var EntityRepository<ChannelCollection> $channelRepository */
        $channelRepository = static::getContainer()->get('channel.repository');

        try {
            $data = [
                'id' => TestDefaults::CHANNEL,
                'domains' => [
                    [
                        'languageId' => $languageId,
                        'currencyId' => Defaults::CURRENCY,
                        'snippetSetId' => $this->getSnippetSetIdForLocale('en-GB'),
                        'url' => $domain,
                    ],
                ],
            ];

            $channelRepository->update([$data], $context);
        } catch (\Exception) {
            // ignore if domain already exists
        }
    }
}

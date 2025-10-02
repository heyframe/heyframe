<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Cart\Order;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartBehavior;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Checkout\Cart\LineItemFactoryHandler\ProductLineItemFactory;
use HeyFrame\Core\Checkout\Cart\Order\OrderConverter;
use HeyFrame\Core\Checkout\Cart\Order\OrderPersister;
use HeyFrame\Core\Checkout\Cart\Order\RecalculationService;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Checkout\Cart\Processor;
use HeyFrame\Core\Checkout\Cart\Transaction\Struct\TransactionCollection;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Checkout\Order\OrderException;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Collector\RuleConditionRegistry;
use HeyFrame\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\CountryAddToChannelTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseHelper\ReflectionHelper;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\System\Channel\Context\ChannelContextService;
use HeyFrame\Core\Test\Integration\PaymentHandler\TestPaymentHandler;
use HeyFrame\Core\Test\Stub\Rule\TrueRule;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Group('slow')]
#[Package('checkout')]
class RecalculationServiceTest extends TestCase
{
    use AdminApiTestBehaviour;
    use CountryAddToChannelTestBehaviour;
    use IntegrationTestBehaviour;

    protected ChannelContext $channelContext;

    protected Context $context;

    protected string $customerId;

    /**
     * @var EntityRepository<OrderCollection>
     */
    private EntityRepository $orderRepository;

    protected function setUp(): void
    {
        $this->orderRepository = static::getContainer()->get('order.repository');

        $this->context = Context::createDefaultContext();

        $priceRuleId = Uuid::randomHex();

        $this->customerId = $this->createCustomer();
        $paymentMethodId = $this->createPaymentMethod($priceRuleId);
        $this->addCountriesToChannel([$this->getValidCountryIdWithTaxes()]);
        $this->channelContext = static::getContainer()->get(ChannelContextFactory::class)->create(
            Uuid::randomHex(),
            TestDefaults::CHANNEL,
            [
                ChannelContextService::CUSTOMER_ID => $this->customerId,
                ChannelContextService::PAYMENT_METHOD_ID => $paymentMethodId,
            ]
        );

        $this->channelContext->setRuleIds([$priceRuleId]);
    }

    #[DataProvider('customLineItemProvider')]
    public function testAddCustomLineItemSdf(LineItem $lineItem, int $positionCount): void
    {
        $cart = $this->generateDemoCart();
        $orderId = $this->persistCart($cart)['orderId'];
        $versionId = $this->createVersionedOrder($orderId);
        $context = Context::createDefaultContext()->createWithVersionId($versionId);

        $this->getContainer()->get(RecalculationService::class)->addCustomLineItem($orderId, $lineItem, $context);

        $criteria = (new Criteria([$orderId]))
            ->addAssociation('lineItems')
            ->addAssociation('deliveries.positions');

        $order = $this->orderRepository->search($criteria, $context)->get($orderId);
        static::assertNotNull($order);

        $lineItems = $order->getLineItems();
        static::assertNotNull($lineItems);
        static::assertCount(3, $lineItems);
    }

    public static function customLineItemProvider(): \Generator
    {
        yield 'line item type custom, shipping cost aware' => [
            (new LineItem(Uuid::randomHex(), LineItem::CUSTOM_LINE_ITEM_TYPE))
                ->setLabel('Test custom line item')
                ->setPriceDefinition(new QuantityPriceDefinition(10)),
            3,
        ];

        yield 'line item type custom, not shipping cost aware' => [
            (new LineItem(Uuid::randomHex(), LineItem::CUSTOM_LINE_ITEM_TYPE))
                ->setLabel('Test custom line item')
                ->setPriceDefinition(new QuantityPriceDefinition(10)),
            2,
        ];
    }

    public function testPersistOrderAndConvertToCart(): void
    {
        $parentProductId = Uuid::randomHex();
        $childProductId = Uuid::randomHex();
        // to test the sorting, the parentId has to be greater than the rootId
        $parentProductId = substr_replace($parentProductId, '0', 0, 1);
        $rootProductId = substr_replace($parentProductId, 'f', 0, 1);

        $cart = $this->generateDemoCart($parentProductId, $rootProductId);

        $cart = $this->addProduct($cart, $childProductId);

        $product1 = $cart->get($parentProductId);
        $product2 = $cart->get($childProductId);

        static::assertNotNull($product1);
        static::assertNotNull($product2);

        $product1->getChildren()->add($product2);
        $cart->remove($childProductId);

        $cart = static::getContainer()->get(Processor::class)
            ->process($cart, $this->channelContext, new CartBehavior());

        $orderId = $this->persistCart($cart)['orderId'];

        $deliveryCriteria = new Criteria();
        $deliveryCriteria->addAssociation('positions');

        $criteria = (new Criteria([$orderId]))
            ->addAssociation('lineItems')
            ->addAssociation('transactions');

        $order = $this->orderRepository->search($criteria, $this->context)->get($orderId);
        static::assertNotNull($order);
        static::assertNotNull($order->getNestedLineItems());

        // check lineItem sorting
        $idx = 0;
        foreach ($order->getNestedLineItems() as $lineItem) {
            if ($idx === 0) {
                static::assertSame($parentProductId, $lineItem->getReferencedId());
            } else {
                static::assertSame($rootProductId, $lineItem->getReferencedId());
            }
            ++$idx;
        }

        $convertedCart = static::getContainer()->get(OrderConverter::class)->convertToCart($order, $this->context);

        // check token
        static::assertNotSame($cart->getToken(), $convertedCart->getToken());
        static::assertTrue(Uuid::isValid($convertedCart->getToken()));

        // check lineItem sorting
        $idx = 0;
        foreach ($convertedCart->getLineItems() as $lineItem) {
            if ($idx === 0) {
                static::assertSame($parentProductId, $lineItem->getId());
            } else {
                static::assertSame($rootProductId, $lineItem->getId());
            }
            ++$idx;
        }
        // set token to be equal for further comparison
        $cart->setToken($convertedCart->getToken());

        // transactions are currently not supported so they are excluded for comparison
        $cart->setTransactions(new TransactionCollection());

        $this->removeExtensions($cart);
        $this->removeExtensions($convertedCart);

        foreach ($cart->getLineItems()->getFlat() as $lineItem) {
            $lineItem->setQuantityInformation(null);
        }

        $this->resetDataTimestamps($cart->getLineItems());

        $cart->setRuleIds([]);
        // The behaviour will be set during the process, therefore we remove it here
        $cart->setBehavior(null);

        // unique identifier is set at runtime to be random uuid
        foreach ($convertedCart->getLineItems()->getFlat() as $lineItem) {
            $lineItem->assign(['uniqueIdentifier' => 'foo']);
        }

        $this->resetPayloadProtection($cart);
        $this->resetPayloadProtection($convertedCart);

        static::assertEquals($cart, $convertedCart);
    }

    public function testRecalculationController(): void
    {
        // create order
        $cart = $this->generateDemoCart();
        $orderId = $this->persistCart($cart)['orderId'];

        // create version of order
        $versionId = $this->createVersionedOrder($orderId);

        // recalculate order
        $this->getBrowser()->request(
            'POST',
            \sprintf('/api/_action/order/%s/recalculate', $orderId),
            [],
            [],
            [
                'HTTP_' . PlatformRequest::HEADER_VERSION_ID => $versionId,
            ]
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        // read order
        $versionContext = $this->context->createWithVersionId($versionId);
        $order = $this->orderRepository->search(new Criteria([$orderId]), $versionContext)->get($orderId);
        static::assertNotNull($order);

        static::assertNotNull($order->getOrderCustomer());

        // recalculate order 2nd time
        $this->getBrowser()->request(
            'POST',
            \sprintf('/api/_action/order/%s/recalculate', $orderId),
            [],
            [],
            [
                'HTTP_' . PlatformRequest::HEADER_VERSION_ID => $versionId,
            ]
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testRecalculationControllerWithNonSystemLanguage(): void
    {
        // create order
        $cart = $this->generateDemoCart();
        $orderId = $this->persistCart($cart, $this->getEnGbLanguageId())['orderId'];

        // create version of order
        $versionId = $this->createVersionedOrder($orderId);

        // recalculate order
        $this->getBrowser()->request(
            'POST',
            \sprintf('/api/_action/order/%s/recalculate', $orderId),
            [],
            [],
            [
                'HTTP_' . PlatformRequest::HEADER_VERSION_ID => $versionId,
            ]
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        // read order
        $versionContext = $this->context->createWithVersionId($versionId);
        $order = $this->orderRepository->search(new Criteria([$orderId]), $versionContext)->get($orderId);
        static::assertNotNull($order);

        static::assertSame($this->getEnGbLanguageId(), $order->getLanguageId());
    }

    public function testFetchOrder(): void
    {
        // create order
        $cart = $this->generateDemoCart();
        $orderId = $this->persistCart($cart)['orderId'];

        static::expectException(OrderException::class);
        static::expectExceptionMessage("Order with id $orderId can not be recalculated because it is in the live version. Please create a new version");

        $service = static::getContainer()->get(RecalculationService::class);

        (new \ReflectionClass($service))
            ->getMethod('fetchOrder')
            ->invoke($service, $orderId, $this->context);
    }

    public function testRecalculationWithDeletedCustomer(): void
    {
        // create order
        $cart = $this->generateDemoCart();
        $orderId = $this->persistCart($cart)['orderId'];

        static::getContainer()->get('customer.repository')->delete([['id' => $this->customerId]], $this->context);

        // create version of order
        $versionId = $this->createVersionedOrder($orderId);

        // recalculate order
        $this->getBrowser()->request(
            'POST',
            \sprintf('/api/_action/order/%s/recalculate', $orderId),
            [],
            [],
            [
                'HTTP_' . PlatformRequest::HEADER_VERSION_ID => $versionId,
            ]
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        // read order
        $versionContext = $this->context->createWithVersionId($versionId);
        $order = $this->orderRepository->search(new Criteria([$orderId]), $versionContext)->get($orderId);
        static::assertNotNull($order);

        static::assertNotNull($order->getOrderCustomer());

        // recalculate order 2nd time
        $this->getBrowser()->request(
            'POST',
            \sprintf('/api/_action/order/%s/recalculate', $orderId),
            [],
            [],
            [
                'HTTP_' . PlatformRequest::HEADER_VERSION_ID => $versionId,
            ]
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testAddProductToOrderTriggersStockUpdate(): void
    {
        // create order
        $cart = $this->generateDemoCart();
        $order = $this->persistCart($cart);
        $orderId = $order['orderId'];
        $oldTotal = $order['total'];

        // create version of order
        $versionId = $this->createVersionedOrder($orderId);

        $productName = 'Test';
        $productPrice = 0.0;
        $productId = $this->addProductToVersionedOrder($productName, $productPrice, $orderId, $versionId, $oldTotal);

        $this->orderRepository
            ->merge($versionId, Context::createDefaultContext());

        $stocks = static::getContainer()->get(Connection::class)
            ->fetchAssociative('SELECT stock, available_stock FROM product WHERE id = :id', ['id' => Uuid::fromHexToBytes($productId)]);

        static::assertIsArray($stocks);

        static::assertSame(4, (int) $stocks['stock']);
        static::assertSame(4, (int) $stocks['available_stock']);
    }

    public function testAddCustomLineItemToOrder(): void
    {
        // create order
        $cart = $this->generateDemoCart();
        ['orderId' => $orderId, 'total' => $oldTotal, 'orderDateTime' => $orderDateTime, 'stateId' => $stateId] = $this->persistCart($cart);

        // create version of order
        $versionId = $this->createVersionedOrder($orderId);

        $this->addCustomLineItemToVersionedOrder($orderId, $versionId, $oldTotal, $orderDateTime, $stateId);
    }

    public function testAddCreditItemToOrder(): void
    {
        // create order
        $cart = $this->generateDemoCart();
        ['orderId' => $orderId, 'total' => $total, 'orderDateTime' => $orderDateTime, 'stateId' => $stateId] = $this->persistCart($cart);

        // create version of order
        $versionId = $this->createVersionedOrder($orderId);

        $this->addCreditItemToVersionedOrder($orderId, $versionId, $total, $orderDateTime, $stateId);
    }

    public function testAddNonExistingPromotionItemToOrder(): void
    {
        // create order
        $cart = $this->generateDemoCart();
        ['orderId' => $orderId] = $this->persistCart($cart);

        // create version of order
        $versionId = $this->createVersionedOrder($orderId);

        $this->getBrowser()->jsonRequest(
            'POST',
            \sprintf('/api/_action/order/%s/promotion-item', $orderId),
            ['code' => 'some-random-code'],
            ['HTTP_' . PlatformRequest::HEADER_VERSION_ID => $versionId],
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        $content = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertCount(1, $content['errors']);

        $errors = array_values($content['errors']);
        static::assertSame($errors[0]['translatedMessage'], '优惠码“some-random-code”未找到');
    }

    public function testCreatedVersionedOrderAndMerge(): void
    {
        // create order
        $cart = $this->generateDemoCart();
        ['orderId' => $orderId, 'total' => $oldTotal, 'orderDateTime' => $orderDateTime] = $this->persistCart($cart);

        // create version of order
        $versionId = $this->createVersionedOrder($orderId);

        $productName = 'Test';
        $productPrice = 0.0;
        $productId = $this->addProductToVersionedOrder(
            $productName,
            $productPrice,
            $orderId,
            $versionId,
            $oldTotal
        );

        // merge versioned order
        $this->getBrowser()->request(
            'POST',
            \sprintf(
                '/api/_action/version/merge/%s/%s',
                static::getContainer()->get(OrderDefinition::class)->getEntityName(),
                $versionId
            )
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());

        // read merged order
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('lineItems');
        $order = $this->orderRepository->search($criteria, $this->context)->get($orderId);
        static::assertNotNull($order);
        static::assertNotNull($order->getLineItems());

        $product = null;
        foreach ($order->getLineItems() as $lineItem) {
            if ($lineItem->getIdentifier() === $productId) {
                $product = $lineItem;
            }
        }

        static::assertNotNull($product);
        static::assertNotNull($product->getPrice());
        static::assertSame($product->getPrice()->getUnitPrice(), $productPrice);
        static::assertNotNull($order->getOrderDateTime());
        static::assertSame($order->getOrderDateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT), $orderDateTime->format(Defaults::STORAGE_DATE_TIME_FORMAT));
    }

    public function testRecalculateOrderWithInactiveProduct(): void
    {
        $inactiveProductId = Uuid::randomHex();
        // create order
        $cart = $this->generateDemoCart($inactiveProductId);
        $orderId = $this->persistCart($cart)['orderId'];

        // create version of order
        $versionId = $this->createVersionedOrder($orderId);
        $versionContext = $this->context->createWithVersionId($versionId);

        static::getContainer()->get(RecalculationService::class)->recalculate($orderId, $versionContext);

        $criteria = (new Criteria([$orderId]))
            ->addAssociation('lineItems')
            ->addAssociation('transactions');

        $order = $this->orderRepository->search($criteria, $this->context)->get($orderId);
        static::assertNotNull($order);

        static::assertSame(239.98, $order->getPrice()->getTotalPrice());
        static::assertSame(239.98, $order->getPrice()->getPositionPrice());

        static::getContainer()->get('product.repository')->update([['id' => $inactiveProductId, 'active' => false]], $this->context);

        static::getContainer()->get(RecalculationService::class)->recalculate($orderId, $versionContext);

        $order = $this->orderRepository->search($criteria, $this->context)->get($orderId);
        static::assertNotNull($order);
        static::assertNotNull($order->getPrice());

        static::assertSame(239.98, $order->getPrice()->getTotalPrice());
        static::assertSame(239.98, $order->getPrice()->getPositionPrice());
    }

    public function testRecalculationControllerWithEmptyLineItems(): void
    {
        // create order
        $cart = $this->generateDemoCart();
        $order = $this->persistCart($cart);

        $orderId = $order['orderId'];

        // create version of order
        $versionId = $this->createVersionedOrder($orderId);
        $versionContext = $this->context->createWithVersionId($versionId);

        // recalculate order
        $this->getBrowser()->request(
            'POST',
            \sprintf('/api/_action/order/%s/recalculate', $orderId),
            [],
            [],
            [
                'HTTP_' . PlatformRequest::HEADER_VERSION_ID => $versionId,
            ]
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('lineItems');

        $order = $this->orderRepository->search($criteria, $versionContext)->get($orderId);
        static::assertNotNull($order);
        static::assertNotNull($order->getLineItems());
        static::assertSame($order->getLineItems()->count(), 2);

        // delete all line items
        $ids = $order->getLineItems()->fmap(fn (OrderLineItemEntity $lineItem) => ['id' => $lineItem->getId()]);
        static::getContainer()->get('order_line_item.repository')->delete(array_values($ids), $versionContext);

        $order = $this->orderRepository->search($criteria, $versionContext)->get($orderId);
        static::assertNotNull($order);
        static::assertNotNull($order->getLineItems());
        static::assertSame($order->getLineItems()->count(), 0);

        // recalculate order 2nd time
        $this->getBrowser()->request(
            'POST',
            \sprintf('/api/_action/order/%s/recalculate', $orderId),
            [],
            [],
            [
                'HTTP_' . PlatformRequest::HEADER_VERSION_ID => $versionId,
            ]
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    protected function getValidCountryIdWithTaxes(): string
    {
        $countryId = $this->getValidCountryId();

        $data = [
            'id' => $countryId,
            'iso' => 'XX',
            'iso3' => 'XXX',
            'active' => true,
            'shippingAvailable' => true,
            'taxFree' => false,
            'position' => 10,
            'displayStateInRegistration' => false,
            'forceStateInRegistration' => false,
            'translations' => [
                [
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'name' => 'Takatuka',
                ],
            ],
        ];

        static::getContainer()->get('country.repository')->upsert(
            [$data],
            $this->context
        );

        return $countryId;
    }

    private function resetPayloadProtection(Cart $cart): void
    {
        // remove delivery information from line items
        $payloadProtection = ReflectionHelper::getProperty(LineItem::class, 'payloadProtection');

        foreach ($cart->getLineItems()->getFlat() as $lineItem) {
            $payloadProtection->setValue($lineItem, []);
        }
    }

    private function resetDataTimestamps(LineItemCollection $items): void
    {
        foreach ($items as $item) {
            $item->setDataTimestamp(null);
            $item->setDataContextHash(null);
            $this->resetDataTimestamps($item->getChildren());
        }
    }

    private function removeExtensions(Cart $cart): void
    {
        $this->removeLineItemsExtension($cart->getLineItems());

        $cart->setExtensions([]);
        $cart->setData(null);
    }

    private function removeLineItemsExtension(LineItemCollection $lineItems): void
    {
        foreach ($lineItems as $lineItem) {
            $lineItem->setExtensions([]);
            $this->removeLineItemsExtension($lineItem->getChildren());
        }
    }

    private function createProduct(string $name, float $price): string
    {
        $productId = Uuid::randomHex();

        $productNumber = Uuid::randomHex();
        $data = [
            'id' => $productId,
            'productNumber' => $productNumber,
            'stock' => 5,
            'name' => $name,
            'productType' => 'type',
            'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => $price]],
            'active' => true,
            'visibilities' => [
                ['channelId' => TestDefaults::CHANNEL, 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
            ],
        ];
        static::getContainer()->get('product.repository')->create([$data], $this->context);

        return $productId;
    }

    private function createCustomer(): string
    {
        $customerId = Uuid::randomHex();

        $customer = [
            'id' => $customerId,
            'number' => '1337',
            'nickname' => 'Mustermann',
            'customerNumber' => '1337',
            'email' => Uuid::randomHex() . '@example.com',
            'password' => TestDefaults::HASHED_PASSWORD,
            'groupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'channelId' => TestDefaults::CHANNEL,
        ];

        static::getContainer()->get('customer.repository')->upsert([$customer], $this->context);

        return $customerId;
    }

    private function generateDemoCart(?string $productId1 = null, ?string $productId2 = null): Cart
    {
        $cart = new Cart(Uuid::randomHex());

        $cart = $this->addProduct($cart, $productId1 ?? Uuid::randomHex());

        return $this->addProduct($cart, $productId2 ?? Uuid::randomHex());
    }

    /**
     * @param array<string, array<string, int|string>|string> $options
     */
    private function addProduct(Cart $cart, string $id, array $options = []): Cart
    {
        $default = [
            'id' => $id,
            'productNumber' => Uuid::randomHex(),
            'productType' => Uuid::randomHex(),
            'price' => [
                ['currencyId' => Defaults::CURRENCY, 'gross' => 119.99, 'net' => 99.99, 'linked' => false],
            ],
            'name' => 'test',
            'stock' => 10,
            'active' => true,
            'visibilities' => [
                ['channelId' => TestDefaults::CHANNEL, 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
            ],
        ];

        $product = array_replace_recursive($default, $options);

        static::getContainer()->get('product.repository')
            ->create([$product], Context::createDefaultContext());

        $lineItem = static::getContainer()->get(ProductLineItemFactory::class)
            ->create(['id' => $id, 'referencedId' => $id], $this->channelContext);
        $lineItem->markUnmodified();

        $lineItem->assign(['uniqueIdentifier' => 'foo']);

        $cart->add($lineItem);

        $cart = static::getContainer()->get(Processor::class)
            ->process($cart, $this->channelContext, new CartBehavior());

        return $cart;
    }

    /**
     * @return array{orderId: string, total: float, orderDateTime: \DateTimeInterface, stateId: string}
     */
    private function persistCart(Cart $cart, ?string $languageId = null): array
    {
        if ($languageId !== null) {
            $context = $this->channelContext->getContext();
            $context->assign([
                'languageIdChain' => array_merge([$languageId], $context->getLanguageIdChain()),
            ]);
        }
        $orderId = static::getContainer()->get(OrderPersister::class)->persist($cart, $this->channelContext);

        $criteria = new Criteria([$orderId]);
        $order = $this->orderRepository->search($criteria, $this->channelContext->getContext())->get($orderId);
        static::assertNotNull($order);

        return [
            'orderId' => $orderId,
            'total' => $order->getPrice()->getTotalPrice(),
            'orderDateTime' => $order->getOrderDateTime(),
            'stateId' => $order->getStateId(),
        ];
    }

    private function createVersionedOrder(string $orderId): string
    {
        $this->getBrowser()->request(
            'POST',
            \sprintf('/api/_action/version/order/%s', $orderId)
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $content = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        $versionId = $content['versionId'];
        static::assertSame($orderId, $content['id']);
        static::assertSame('order', $content['entity']);
        static::assertTrue(Uuid::isValid($versionId));

        return $versionId;
    }

    private function addProductToVersionedOrder(
        string $productName,
        float $productPrice,
        string $orderId,
        string $versionId,
        float $oldTotal
    ): string {
        $productId = $this->createProduct($productName, $productPrice);

        // add product to order
        $this->getBrowser()->request(
            'POST',
            \sprintf(
                '/api/_action/order/%s/product/%s',
                $orderId,
                $productId
            ),
            [],
            [],
            [
                'HTTP_' . PlatformRequest::HEADER_VERSION_ID => $versionId,
            ]
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());

        $this->getBrowser()->request(
            'POST',
            \sprintf('/api/_action/order/%s/recalculate', $orderId),
            [],
            [],
            [
                'HTTP_' . PlatformRequest::HEADER_VERSION_ID => $versionId,
            ]
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());

        // read versioned order
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('lineItems');
        $order = $this->orderRepository->search($criteria, $this->context->createWithVersionId($versionId))->get($orderId);
        static::assertNotNull($order);
        static::assertNotNull($order->getLineItems());

        $product = $order->getLineItems()->firstWhere(
            static fn (OrderLineItemEntity $item) => $item->getIdentifier() === $productId,
        );

        static::assertNotNull($product);
        static::assertNotNull($product->getPrice());

        static::assertSame($oldTotal, $order->getAmountTotal());

        return $productId;
    }

    private function addCustomLineItemToVersionedOrder(string $orderId, string $versionId, float $oldTotal, \DateTimeInterface $orderDateTime, string $stateId): void
    {
        $identifier = Uuid::randomHex();
        $data = [
            'identifier' => $identifier,
            'type' => LineItem::CUSTOM_LINE_ITEM_TYPE,
            'quantity' => 10,
            'label' => 'example label',
            'description' => 'example description',
            'priceDefinition' => [
                'price' => 27.99,
                'quantity' => 10,
                'isCalculated' => false,
                'precision' => 2,
            ],
        ];

        // add product to order
        $this->getBrowser()->jsonRequest(
            'POST',
            \sprintf('/api/_action/order/%s/lineItem', $orderId),
            $data,
            [
                'HTTP_' . PlatformRequest::HEADER_VERSION_ID => $versionId,
            ],
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());

        // read versioned order
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('lineItems');
        $order = $this->orderRepository->search($criteria, $this->context->createWithVersionId($versionId))->get($orderId);
        static::assertNotNull($order);
        static::assertNotNull($order->getLineItems());

        $customLineItem = null;
        foreach ($order->getLineItems() as $lineItem) {
            if ($lineItem->getIdentifier() === $identifier) {
                $customLineItem = $lineItem;
            }
        }

        static::assertNotNull($customLineItem);
        static::assertNotNull($customLineItem->getPrice());
        static::assertSame($customLineItem->getPrice()->getUnitPrice(), 27.99);
        static::assertSame($customLineItem->getPrice()->getQuantity(), 10);
        static::assertSame($customLineItem->getPrice()->getTotalPrice(), 279.9);

        static::assertSame($order->getOrderDateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT), $orderDateTime->format(Defaults::STORAGE_DATE_TIME_FORMAT));
        static::assertSame($customLineItem->getPrice()->getTotalPrice() + $oldTotal, $order->getAmountTotal());
        static::assertSame($stateId, $order->getStateId());
    }

    private function addCreditItemToVersionedOrder(string $orderId, string $versionId, float $oldTotal, \DateTimeInterface $orderDateTime, string $stateId): void
    {
        $orderRepository = $this->orderRepository;

        $identifier = Uuid::randomHex();
        $creditAmount = -10.0;
        $data = [
            'identifier' => $identifier,
            'type' => LineItem::CREDIT_LINE_ITEM_TYPE,
            'quantity' => 1,
            'label' => 'awesome credit',
            'description' => 'schubbidu',
            'priceDefinition' => [
                'price' => $creditAmount,
                'quantity' => 1,
                'isCalculated' => false,
                'precision' => 2,
            ],
        ];

        // add credit item to order
        $this->getBrowser()->jsonRequest(
            'POST',
            \sprintf('/api/_action/order/%s/creditItem', $orderId),
            $data,
            [
                'HTTP_' . PlatformRequest::HEADER_VERSION_ID => $versionId,
            ],
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());

        // read versioned order
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('lineItems');
        $order = $orderRepository->search($criteria, $this->context->createWithVersionId($versionId))->get($orderId);
        static::assertNotEmpty($order);
        static::assertNotNull($order->getLineItems());
        static::assertSame($oldTotal + $creditAmount, $order->getAmountTotal());

        $creditItem = $order->getLineItems()->filterByProperty('identifier', $identifier)->first();
        static::assertNotNull($creditItem);
        $price = $creditItem->getPrice();
        static::assertNotNull($price);

        static::assertSame($creditAmount, $price->getTotalPrice());
        static::assertSame($order->getOrderDateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT), $orderDateTime->format(Defaults::STORAGE_DATE_TIME_FORMAT));
        static::assertSame($stateId, $order->getStateId());
    }

    private function addPromotionItemToVersionedOrder(string $orderId, string $versionId, string $code, \DateTimeInterface $orderDateTime, string $stateId): OrderEntity
    {
        $orderRepository = $this->orderRepository;

        $data = [
            'code' => $code,
        ];

        // add promotion item to order
        $this->getBrowser()->jsonRequest(
            'POST',
            \sprintf('/api/_action/order/%s/promotion-item', $orderId),
            $data,
            [
                'HTTP_' . PlatformRequest::HEADER_VERSION_ID => $versionId,
            ],
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        // read versioned order
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('lineItems');
        $order = $orderRepository->search($criteria, $this->context->createWithVersionId($versionId))->get($orderId);
        static::assertNotEmpty($order);
        static::assertNotNull($order->getLineItems());
        static::assertCount(3, $order->getLineItems());
        static::assertSame($order->getOrderDateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT), $orderDateTime->format(Defaults::STORAGE_DATE_TIME_FORMAT));

        $promotionItem = $order->getLineItems()->filterByProperty('referencedId', $code)->first();

        static::assertNotNull($promotionItem);

        $content = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        static::assertCount(1, $content['errors']);

        $errors = array_values($content['errors']);
        static::assertSame($errors[0]['translatedMessage'], '折扣“GET5”已被添加');
        static::assertSame($stateId, $order->getStateId());

        return $order;
    }

    /**
     * @return array{0: OrderEntity, 1: array<mixed>}
     */
    private function applyAutomaticPromotions(string $orderId, string $versionId, ?string $promotionId): array
    {
        $orderRepository = $this->orderRepository;

        $data = [
            'skipAutomaticPromotions' => false,
        ];

        // add promotion item to order
        $this->getBrowser()->jsonRequest(
            'POST',
            \sprintf('/api/_action/order/%s/applyAutomaticPromotions', $orderId),
            $data,
            [
                'HTTP_' . PlatformRequest::HEADER_VERSION_ID => $versionId,
            ],
        );
        $response = $this->getBrowser()->getResponse();

        static::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        // read versioned order
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('lineItems');
        $order = $orderRepository->search($criteria, $this->context->createWithVersionId($versionId))->get($orderId);
        static::assertNotEmpty($order);
        static::assertNotNull($order->getLineItems());
        static::assertCount(3, $order->getLineItems());

        $promotionItem = $order->getLineItems()->filterByType('promotion')->first();
        if ($promotionId) {
            static::assertNotNull($promotionItem);
            static::assertNotNull($promotionItem->getPayload());
            static::assertSame($promotionItem->getPayload()['promotionId'], $promotionId);
        } else {
            static::assertNull($promotionItem);
        }

        $content = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        return [$order, $content];
    }

    private function createPaymentMethod(string $ruleId): string
    {
        $paymentMethodId = Uuid::randomHex();
        $ruleRegistry = static::getContainer()->get(RuleConditionRegistry::class);
        $prop = ReflectionHelper::getProperty(RuleConditionRegistry::class, 'rules');
        $prop->setValue($ruleRegistry, array_merge($prop->getValue($ruleRegistry), ['true' => new TrueRule()]));

        $data = [
            'id' => $paymentMethodId,
            'handlerIdentifier' => TestPaymentHandler::class,
            'name' => 'Payment',
            'technicalName' => 'payment_test',
            'active' => true,
            'position' => 0,
            'availabilityRule' => [
                'id' => $ruleId,
                'name' => 'true',
                'priority' => 0,
                'conditions' => [
                    [
                        'type' => 'true',
                    ],
                ],
            ],
            'channels' => [
                [
                    'id' => TestDefaults::CHANNEL,
                ],
            ],
        ];

        static::getContainer()->get('payment_method.repository')->create([$data], $this->context);

        return $paymentMethodId;
    }
}

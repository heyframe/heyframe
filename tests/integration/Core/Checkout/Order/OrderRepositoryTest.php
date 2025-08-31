<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Order;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartBehavior;
use HeyFrame\Core\Checkout\Cart\CartRuleLoader;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\Order\OrderPersister;
use HeyFrame\Core\Checkout\Cart\Order\OrderPersisterInterface;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Checkout\Cart\Processor;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Checkout\Order\OrderStates;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\CountryAddToChannelTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\Context\AbstractChannelContextFactory;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\System\Channel\Context\ChannelContextService;
use HeyFrame\Core\System\StateMachine\Loader\InitialStateIdLoader;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
class OrderRepositoryTest extends TestCase
{
    use CountryAddToChannelTestBehaviour;
    use IntegrationTestBehaviour;

    /**
     * @var EntityRepository<OrderCollection>
     */
    private EntityRepository $orderRepository;

    private OrderPersisterInterface $orderPersister;

    private Processor $processor;

    /**
     * @var EntityRepository<CustomerCollection>
     */
    private EntityRepository $customerRepository;

    private AbstractChannelContextFactory $channelContextFactory;

    protected function setUp(): void
    {
        $this->orderRepository = static::getContainer()->get('order.repository');
        $this->orderPersister = static::getContainer()->get(OrderPersister::class);
        $this->customerRepository = static::getContainer()->get('customer.repository');
        $this->processor = static::getContainer()->get(Processor::class);
        $this->channelContextFactory = static::getContainer()->get(ChannelContextFactory::class);
    }

    public function testCreateOrder(): void
    {
        $orderId = Uuid::randomHex();
        $defaultContext = Context::createDefaultContext();
        $orderData = $this->getOrderData($orderId);
        $this->orderRepository->create($orderData, $defaultContext);

        $nestedCriteria2 = new Criteria();
        $nestedCriteria2->addAssociation('addresses');

        $criteria = new Criteria([$orderId]);

        /** @var OrderEntity|null $order */
        $order = $this->orderRepository->search($criteria, $defaultContext)->first();

        static::assertNotNull($order);
        static::assertNotNull($order->getOrderCustomer());
        static::assertSame($orderId, $order->get('id'));
        static::assertSame('test@example.com', $order->getOrderCustomer()->getEmail());
    }

    /**
     * Regression from NEXT-20340
     */
    public function testDeleteOrderWithoutCustomer(): void
    {
        $orderId = Uuid::randomHex();
        $defaultContext = Context::createDefaultContext();
        $orderData = $this->getOrderData($orderId);

        unset($orderData[0]['orderCustomer']['customer']);

        $this->orderRepository->create($orderData, $defaultContext);

        $this->orderRepository->delete([['id' => $orderId]], $defaultContext);

        $criteria = new Criteria([$orderId]);
        $order = $this->orderRepository->searchIds($criteria, $defaultContext);

        static::assertEmpty($order->getIds());
    }

    public function testDeleteOrder(): void
    {
        $token = Uuid::randomHex();
        $cart = new Cart($token);

        $id = Uuid::randomHex();

        $product = [
            'id' => $id,
            'name' => 'test',
            'price' => [
                ['currencyId' => Defaults::CURRENCY, 'gross' => 119.99],
            ],
            'productNumber' => Uuid::randomHex(),
            'stock' => 10,
            'productType' => 'test',
            'active' => true,
            'visibilities' => [
                ['channelId' => TestDefaults::CHANNEL, 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
            ],
        ];

        static::getContainer()->get('product.repository')
            ->create([$product], Context::createDefaultContext());

        $cart->add(
            (new LineItem($id, LineItem::PRODUCT_LINE_ITEM_TYPE, $id))
                ->setGood(true)
                ->setStackable(true)
        );

        $customerId = $this->createCustomer();

        $this->addCountriesToChannel();

        $context = $this->channelContextFactory->create(
            Uuid::randomHex(),
            TestDefaults::CHANNEL,
            [
                ChannelContextService::CUSTOMER_ID => $customerId,
            ]
        );

        static::getContainer()->get(CartRuleLoader::class)->loadByToken($context, $context->getToken());

        $cart = $this->processor->process($cart, $context, new CartBehavior());

        $id = $this->orderPersister->persist($cart, $context);

        $count = static::getContainer()->get(Connection::class)->fetchAllAssociative('SELECT * FROM `order` WHERE id = :id', ['id' => Uuid::fromHexToBytes($id)]);
        static::assertCount(1, $count);

        $this->orderRepository->delete([
            ['id' => $id],
        ], Context::createDefaultContext());

        $count = static::getContainer()->get(Connection::class)->fetchAllAssociative('SELECT * FROM `order` WHERE id = :id', ['id' => Uuid::fromHexToBytes($id)]);
        static::assertCount(0, $count);
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

        $this->customerRepository->upsert([$customer], Context::createDefaultContext());

        return $customerId;
    }

    /**
     * @return array<array<mixed>>
     */
    private function getOrderData(string $orderId): array
    {
        $orderLineItemId = Uuid::randomHex();

        $order = [
            [
                'id' => $orderId,
                'itemRounding' => json_decode(json_encode(new CashRoundingConfig(2, 0.01, true), \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR),
                'totalRounding' => json_decode(json_encode(new CashRoundingConfig(2, 0.01, true), \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR),
                'orderDateTime' => (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                'price' => new CartPrice(10, 10, 10),
                'shippingCosts' => new CalculatedPrice(10, 10),
                'stateId' => static::getContainer()->get(InitialStateIdLoader::class)->get(OrderStates::STATE_MACHINE),
                'paymentMethodId' => $this->getValidPaymentMethodId(),
                'currencyId' => Defaults::CURRENCY,
                'currencyFactor' => 1,
                'channelId' => TestDefaults::CHANNEL,
                'lineItems' => [
                    [
                        'id' => $orderLineItemId,
                        'identifier' => 'test',
                        'quantity' => 1,
                        'type' => 'test',
                        'label' => 'test',
                        'price' => new CalculatedPrice(10, 10),
                        'priceDefinition' => new QuantityPriceDefinition(10),
                        'good' => true,
                    ],
                ],
                'deepLinkCode' => 'BwvdEInxOHBbwfRw6oHF1Q_orfYeo9RY',
                'orderCustomer' => [
                    'email' => 'test@example.com',
                    'nickname' => 'Hill',
                    'title' => 'Doc',
                    'customerNumber' => 'Test',
                    'customer' => [
                        'email' => 'test@example.com',
                        'nickname' => 'Hill',
                        'customerNumber' => 'Test',
                        'guest' => true,
                        'group' => ['name' => 'testse2323'],
                        'channelId' => TestDefaults::CHANNEL,
                    ],
                ],
            ],
        ];

        return $order;
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Order\Channel;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Cart\Channel\CartService;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Transaction\Struct\Transaction;
use HeyFrame\Core\Checkout\Cart\Transaction\Struct\TransactionCollection;
use HeyFrame\Core\Checkout\Order\Channel\OrderService;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\CountryAddToChannelTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\MailTemplateTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\Aggregate\ChannelDomain\ChannelDomainDefinition;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\System\Channel\Context\ChannelContextService;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @internal
 */
#[Package('checkout')]
#[Group('slow')]
class OrderServiceTest extends TestCase
{
    use CountryAddToChannelTestBehaviour;
    use IntegrationTestBehaviour;
    use MailTemplateTestBehaviour;

    private ChannelContext $channelContext;

    private OrderService $orderService;

    /**
     * @var EntityRepository<OrderCollection>
     */
    private EntityRepository $orderRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = static::getContainer()->get(OrderService::class);

        $this->orderRepository = static::getContainer()->get('order.repository');

        $this->cleanDefaultChannelDomain();
        $this->addCountriesToChannel();

        $contextFactory = static::getContainer()->get(ChannelContextFactory::class);
        $this->channelContext = $contextFactory->create(
            '',
            TestDefaults::CHANNEL,
            [ChannelContextService::CUSTOMER_ID => $this->createCustomer('Jon')]
        );
    }

    public function testOrderTransactionStateTransition(): void
    {
        $orderId = $this->performOrder();

        // getting the id of the order transaction
        $criteria = new Criteria([$orderId]);

        $criteria->addAssociation('transactions.stateMachineState');

        /** @var OrderEntity $order */
        $order = $this->orderRepository->search($criteria, $this->channelContext->getContext())->first();
        static::assertNotNull($transactions = $order->getTransactions());
        static::assertNotNull($transaction = $transactions->first());
        $orderTransactionId = $transaction->getId();

        $this->orderService->orderTransactionStateTransition(
            $orderTransactionId,
            'remind',
            new RequestDataBag(),
            $this->channelContext->getContext()
        );

        /** @var OrderEntity $updatedOrder */
        $updatedOrder = $this->orderRepository->search($criteria, $this->channelContext->getContext())->first();
        static::assertNotNull($transactions = $updatedOrder->getTransactions());
        static::assertNotNull($transaction = $transactions->first());
        static::assertNotNull($transaction->getStateMachineState());
        $updatedTransactionState = $transaction->getStateMachineState()->getTechnicalName();

        static::assertSame('reminded', $updatedTransactionState);
    }

    public function testCreateOrder(): void
    {
        $data = new RequestDataBag(['tos' => true]);
        $this->fillCart($this->channelContext->getToken());

        $orderId = $this->orderService->createOrder($data, $this->channelContext);

        $criteria = new Criteria([$orderId]);

        $criteria->addAssociation('stateMachineState');

        /** @var OrderEntity $newlyCreatedOrder */
        $newlyCreatedOrder = $this->orderRepository->search($criteria, $this->channelContext->getContext())->first();

        static::assertInstanceOf(OrderEntity::class, $newlyCreatedOrder);
        static::assertSame($orderId, $newlyCreatedOrder->getId());
    }

    public function testOrderStateTransition(): void
    {
        $orderId = $this->performOrder();

        $this->orderService->orderStateTransition($orderId, 'cancel', new ParameterBag(), $this->channelContext->getContext());

        $criteria = new Criteria([$orderId]);

        $criteria->addAssociation('stateMachineState');

        /** @var OrderEntity $cancelledOrder */
        $cancelledOrder = $this->orderRepository->search($criteria, $this->channelContext->getContext())->first();
        $state = $cancelledOrder->getStateMachineState();

        static::assertNotNull($state);
        static::assertSame('cancelled', $state->getTechnicalName());
    }

    private function performOrder(): string
    {
        $data = new RequestDataBag(['tos' => true]);
        $this->fillCart($this->channelContext->getToken());

        return $this->orderService->createOrder($data, $this->channelContext);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createCustomer(string $nickname, array $options = []): string
    {
        $customerId = Uuid::randomHex();

        $customer = [
            'id' => $customerId,
            'channelId' => TestDefaults::CHANNEL,
            'groupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'email' => Uuid::randomHex() . '@example.com',
            'password' => TestDefaults::HASHED_PASSWORD,
            'nickname' => $nickname,
            'customerNumber' => '12345',
        ];

        $customer = array_merge_recursive($customer, $options);

        static::getContainer()->get('customer.repository')->create([$customer], Context::createDefaultContext());

        return $customerId;
    }

    private function fillCart(string $contextToken): void
    {
        $cart = static::getContainer()->get(CartService::class)->createNew($contextToken);

        $productId = $this->createProduct();
        $cart->add(new LineItem('lineItem1', LineItem::PRODUCT_LINE_ITEM_TYPE, $productId));
        $cart->setTransactions($this->createTransaction());
    }

    private function createProduct(): string
    {
        $productId = Uuid::randomHex();

        $product = [
            'id' => $productId,
            'name' => 'Test product',
            'productNumber' => '123456789',
            'productType' => 'type',
            'stock' => 1,
            'price' => [
                ['currencyId' => Defaults::CURRENCY, 'gross' => 19.99],
            ],
            'visibilities' => [
                [
                    'id' => $productId,
                    'channelId' => TestDefaults::CHANNEL,
                    'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                ],
            ],
        ];

        static::getContainer()->get('product.repository')->create([$product], Context::createDefaultContext());

        return $productId;
    }

    private function createTransaction(): TransactionCollection
    {
        return new TransactionCollection([
            new Transaction(
                new CalculatedPrice(
                    13.37,
                    13.37,
                ),
                $this->getValidPaymentMethodId()
            ),
        ]);
    }

    private function cleanDefaultChannelDomain(): void
    {
        $connection = static::getContainer()->get(Connection::class);

        $connection->delete(ChannelDomainDefinition::ENTITY_NAME, [
            'channel_id' => Uuid::fromHexToBytes(TestDefaults::CHANNEL),
        ]);
    }
}

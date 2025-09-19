<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Order\Listener;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use HeyFrame\Core\Checkout\Order\Event\OrderStateMachineStateChangeEvent;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Checkout\Payment\Cart\PaymentHandler\WeChatPaymentHandler;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseHelper\CallableClass;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionActions;
use HeyFrame\Core\System\StateMachine\StateMachineRegistry;
use HeyFrame\Core\System\StateMachine\Transition;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
class OrderStateChangeEventListenerTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testTriggerTransactionEvents(): void
    {
        $ids = new IdsCollection();

        $this->createCustomer($ids);
        $this->createOrder($ids);

        $this->assertEvent('state_leave.order_transaction.state.open');
        $this->assertEvent('state_enter.order_transaction.state.in_progress');

        static::getContainer()
            ->get(StateMachineRegistry::class)
            ->transition(
                new Transition(
                    OrderTransactionDefinition::ENTITY_NAME,
                    $ids->get('transaction'),
                    StateMachineTransitionActions::ACTION_PROCESS,
                    'stateId'
                ),
                Context::createDefaultContext()
            );
    }

    public function testTriggerOrderEvent(): void
    {
        $ids = new IdsCollection();

        $this->createCustomer($ids);
        $this->createOrder($ids);
        $this->assertEvent('state_leave.order.state.open');
        $this->assertEvent('state_enter.order.state.in_progress');

        static::getContainer()
            ->get(StateMachineRegistry::class)
            ->transition(
                new Transition(
                    OrderDefinition::ENTITY_NAME,
                    $ids->get('order'),
                    StateMachineTransitionActions::ACTION_PROCESS,
                    'stateId'
                ),
                Context::createDefaultContext()
            );
    }

    private function assertEvent(string $event): void
    {
        $listener = $this->getMockBuilder(CallableClass::class)->getMock();
        $listener->expects($this->once())->method('__invoke');

        static::getContainer()
            ->get('event_dispatcher')
            ->addListener($event, $listener);
    }

    private function createOrder(IdsCollection $ids): void
    {
        $data = [
            'id' => $ids->create('order'),
            'orderNumber' => Uuid::randomHex(),
            'billingAddressId' => $ids->create('billing-address'),
            'currencyId' => Defaults::CURRENCY,
            'languageId' => Defaults::LANGUAGE_SYSTEM,
            'channelId' => TestDefaults::CHANNEL,
            'orderDateTime' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            'currencyFactor' => 1,
            'stateId' => $this->getStateId('open', 'order.state'),
            'price' => new CartPrice(200, 200, 200),
            'itemRounding' => json_decode(json_encode(new CashRoundingConfig(2, 0.01, true), \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR),
            'totalRounding' => json_decode(json_encode(new CashRoundingConfig(2, 0.01, true), \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR),
            'ruleIds' => [$ids->get('rule')],
            'orderCustomer' => [
                'id' => $ids->get('order_customer'),
                'email' => 'test',
                'nickname' => 'test',
                'name' => 'test',
                'customerId' => $ids->get('customer'),
            ],
            'lineItems' => [
                [
                    'id' => $ids->create('line-item'),
                    'identifier' => $ids->create('line-item'),
                    'quantity' => 1,
                    'label' => 'label',
                    'type' => LineItem::CUSTOM_LINE_ITEM_TYPE,
                    'price' => new CalculatedPrice(200, 200),
                    'priceDefinition' => new QuantityPriceDefinition(200),
                ],
            ],
            'transactions' => [
                [
                    'id' => $ids->create('transaction'),
                    'paymentMethodId' => $this->getPrePaymentMethodId(),
                    'stateId' => $this->getStateId('open', 'order_transaction.state'),
                    'amount' => new CalculatedPrice(200, 200),
                ],
            ],
        ];

        static::getContainer()->get('order.repository')
            ->create([$data], Context::createDefaultContext());
    }

    private function createCustomer(IdsCollection $ids): string
    {
        $customer = [
            'id' => $ids->get('customer'),
            'number' => '1337',
            'name' => 'Max',
            'nickname' => 'Mustermann',
            'customerNumber' => '1337',
            'email' => Uuid::randomHex() . '@example.com',
            'password' => TestDefaults::HASHED_PASSWORD,
            'groupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'channelId' => TestDefaults::CHANNEL,
        ];

        static::getContainer()
            ->get('customer.repository')
            ->upsert([$customer], Context::createDefaultContext());

        return $ids->get('customer');
    }

    private function getPrePaymentMethodId(): string
    {
        /** @var EntityRepository<PaymentMethodCollection> $repository */
        $repository = static::getContainer()->get('payment_method.repository');

        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('active', true))
            ->addFilter(new EqualsFilter('handlerIdentifier', WeChatPaymentHandler::class));

        $id = $repository->searchIds($criteria, Context::createDefaultContext())->getIds()[0];
        static::assertIsString($id);

        return $id;
    }

    private function getStateId(string $state, string $machine): ?string
    {
        return static::getContainer()->get(Connection::class)
            ->fetchOne('
                SELECT LOWER(HEX(state_machine_state.id))
                FROM state_machine_state
                    INNER JOIN  state_machine
                    ON state_machine.id = state_machine_state.state_machine_id
                    AND state_machine.technical_name = :machine
                WHERE state_machine_state.technical_name = :state
            ', [
                'state' => $state,
                'machine' => $machine,
            ]);
    }
}

/**
 * @internal
 */
#[Package('checkout')]
class RuleValidator extends CallableClass
{
    public ?OrderStateMachineStateChangeEvent $event;

    public function __invoke(): void
    {
        $this->event = func_get_arg(0);
    }
}

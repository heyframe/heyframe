<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Order;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\Event\ChannelContextAssembledEvent;
use HeyFrame\Core\Checkout\Cart\Order\Transformer\CartTransformer;
use HeyFrame\Core\Checkout\Cart\Order\Transformer\CustomerTransformer;
use HeyFrame\Core\Checkout\Cart\Order\Transformer\LineItemTransformer;
use HeyFrame\Core\Checkout\Cart\Order\Transformer\TransactionTransformer;
use HeyFrame\Core\Checkout\CheckoutPermissions;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Checkout\Order\OrderException;
use HeyFrame\Core\Checkout\Order\OrderStates;
use HeyFrame\Core\Content\Rule\RuleCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\AbstractChannelContextFactory;
use HeyFrame\Core\System\Channel\Context\ChannelContextService;
use HeyFrame\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use HeyFrame\Core\System\StateMachine\Loader\InitialStateIdLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Package('checkout')]
class OrderConverter
{
    final public const CART_CONVERTED_TO_ORDER_EVENT = 'cart.convertedToOrder.event';

    final public const CART_TYPE = 'recalculation';

    final public const ORIGINAL_ID = 'originalId';

    final public const ORIGINAL_ORDER_NUMBER = 'originalOrderNumber';

    final public const ORIGINAL_PRIMARY_ORDER_TRANSACTION = 'originalPrimaryOrderTransaction';

    final public const ADMIN_EDIT_ORDER_PERMISSIONS = [
        CheckoutPermissions::ALLOW_PRODUCT_PRICE_OVERWRITES => true,
        CheckoutPermissions::SKIP_PRODUCT_RECALCULATION => true,
        CheckoutPermissions::SKIP_PRODUCT_STOCK_VALIDATION => true,
        CheckoutPermissions::KEEP_INACTIVE_PRODUCT => true,
        CheckoutPermissions::PIN_MANUAL_PROMOTIONS => true,
        CheckoutPermissions::PIN_AUTOMATIC_PROMOTIONS => true,
        CheckoutPermissions::SKIP_CART_PERSISTENCE => true,
        CheckoutPermissions::SKIP_PRIMARY_ORDER_IDS => true,
        CheckoutPermissions::AUTOMATIC_PROMOTION_DELETION_NOTICES => true,
    ];

    /**
     * @internal
     *
     * @param EntityRepository<CustomerCollection> $customerRepository
     * @param EntityRepository<RuleCollection> $ruleRepository
     */
    public function __construct(
        protected EntityRepository $customerRepository,
        protected AbstractChannelContextFactory $channelContextFactory,
        protected EventDispatcherInterface $eventDispatcher,
        private readonly NumberRangeValueGeneratorInterface $numberRangeValueGenerator,
        private readonly InitialStateIdLoader $initialStateIdLoader,
        private readonly EntityRepository $ruleRepository,
    ) {
    }

    /**
     * @throws OrderException
     *
     * @return array<string, mixed|float|string|array<int, array<string, string|int|bool|mixed>>|null>
     */
    public function convertToOrder(Cart $cart, ChannelContext $context, OrderConversionContext $conversionContext): array
    {
        $data = CartTransformer::transform(
            $cart,
            $context,
            $this->initialStateIdLoader->get(OrderStates::STATE_MACHINE),
            $conversionContext->shouldIncludePersistentData(),
        );

        if ($conversionContext->shouldIncludeCustomer()) {
            $customer = $context->getCustomer();
            if ($customer === null) {
                throw CartException::customerNotLoggedIn();
            }

            $data['orderCustomer'] = CustomerTransformer::transform($customer);
            $data['orderCustomer']['customer'] = [
                'id' => $customer->getId(),
                'lastPaymentMethodId' => $context->getPaymentMethod()->getId(),
            ];
            unset($data['orderCustomer']['customerId']);
        }

        $data['languageId'] = $context->getLanguageId();

        $convertedLineItems = LineItemTransformer::transformCollection($cart->getLineItems());

        if ($conversionContext->shouldIncludeTransactions()) {
            $data['transactions'] = TransactionTransformer::transformCollection(
                $cart->getTransactions(),
                $this->initialStateIdLoader->get(OrderTransactionStates::STATE_MACHINE),
                $context->getContext()
            );

            if (!$cart->getBehavior()?->hasPermission(CheckoutPermissions::SKIP_PRIMARY_ORDER_IDS) && $cart->getTransactions()->count() > 0) {
                $data['transactions'][0]['id'] ??= Uuid::randomHex();
                $data['primaryOrderTransactionId'] = $data['transactions'][0]['id'];
            }
        }

        $data['lineItems'] = array_values($convertedLineItems);

        $idStruct = $cart->getExtensionOfType(self::ORIGINAL_ID, IdStruct::class);
        $data['id'] = $idStruct ? $idStruct->getId() : Uuid::randomHex();

        if ($conversionContext->shouldIncludeOrderNumber()) {
            $orderNumberStruct = $cart->getExtensionOfType(self::ORIGINAL_ORDER_NUMBER, IdStruct::class);
            if ($orderNumberStruct !== null) {
                $data['orderNumber'] = $orderNumberStruct->getId();
            } else {
                $data['orderNumber'] = $this->numberRangeValueGenerator->getValue(
                    OrderDefinition::ENTITY_NAME,
                    $context->getContext(),
                    $context->getChannelId()
                );
            }
        }

        $data['ruleIds'] = $context->getRuleIds();

        $event = new CartConvertedEvent($cart, $data, $context, $conversionContext);
        $this->eventDispatcher->dispatch($event);

        return $event->getConvertedCart();
    }

    /**
     * @throws CartException
     */
    public function convertToCart(OrderEntity $order, Context $context): Cart
    {
        if ($order->getLineItems() === null) {
            throw OrderException::missingAssociation('lineItems');
        }

        $cart = new Cart(Uuid::randomHex());
        $cart->setPrice($order->getPrice());
        $cart->setSource($order->getSource());
        $cart->addExtension(self::ORIGINAL_ID, new IdStruct($order->getId()));
        $orderNumber = $order->getOrderNumber();
        if ($orderNumber === null) {
            throw OrderException::missingOrderNumber($order->getId());
        }

        $cart->addExtension(self::ORIGINAL_ORDER_NUMBER, new IdStruct($orderNumber));
        /* NEXT-708 support:
            - transactions
        */

        $lineItems = LineItemTransformer::transformFlatToNested($order->getLineItems());

        $cart->addLineItems($lineItems);

        if ($order->getPrimaryOrderTransactionId()) {
            $cart->addExtension(self::ORIGINAL_PRIMARY_ORDER_TRANSACTION, new IdStruct($order->getPrimaryOrderTransactionId()));
        }

        $event = new OrderConvertedEvent($order, $cart, $context);
        $this->eventDispatcher->dispatch($event);

        return $event->getConvertedCart();
    }

    /**
     * @param array<string, array<string, bool>|string> $overrideOptions
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function assembleChannelContext(OrderEntity $order, Context $context, array $overrideOptions = []): ChannelContext
    {
        if ($order->getTransactions() === null) {
            throw OrderException::missingAssociation('transactions');
        }
        if ($order->getOrderCustomer() === null) {
            throw OrderException::missingAssociation('orderCustomer');
        }

        $customerId = $order->getOrderCustomer()->getCustomerId();
        $customer = null;

        if ($customerId) {
            $customerCriteria = (new Criteria([$customerId]));

            $customer = $this->customerRepository->search($customerCriteria, $context)->getEntities()->first();
        }

        $options = [
            ChannelContextService::CURRENCY_ID => $order->getCurrencyId(),
            ChannelContextService::LANGUAGE_ID => $order->getLanguageId(),
            ChannelContextService::CUSTOMER_ID => $customerId,
            ChannelContextService::CUSTOMER_GROUP_ID => $customer?->getGroupId(),
            ChannelContextService::PERMISSIONS => self::ADMIN_EDIT_ORDER_PERMISSIONS,
            ChannelContextService::VERSION_ID => $context->getVersionId(),
        ];

        foreach ($order->getTransactions() as $transaction) {
            $options[ChannelContextService::PAYMENT_METHOD_ID] = $transaction->getPaymentMethodId();
            if (
                $transaction->getStateMachineState() !== null
                && $transaction->getStateMachineState()->getTechnicalName() !== OrderTransactionStates::STATE_PAID
                && $transaction->getStateMachineState()->getTechnicalName() !== OrderTransactionStates::STATE_CANCELLED
                && $transaction->getStateMachineState()->getTechnicalName() !== OrderTransactionStates::STATE_FAILED
            ) {
                break;
            }
        }

        $options = array_merge($options, $overrideOptions);

        $channelContext = $this->channelContextFactory->create(Uuid::randomHex(), $order->getChannelId(), $options);
        $channelContext->getContext()->addExtensions($context->getExtensions());
        $channelContext->addState(...$context->getStates());

        if ($context->hasState(Context::SKIP_TRIGGER_FLOW)) {
            $channelContext->getContext()->addState(Context::SKIP_TRIGGER_FLOW);
        }

        if ($order->getItemRounding() !== null) {
            $channelContext->setItemRounding($order->getItemRounding());
        }

        if ($order->getTotalRounding() !== null) {
            $channelContext->setTotalRounding($order->getTotalRounding());
        }

        if ($order->getRuleIds() !== null) {
            $channelContext->setRuleIds($order->getRuleIds());
            $channelContext->setAreaRuleIds($this->fetchRuleAreas($order->getRuleIds(), $context));
        }

        $event = new ChannelContextAssembledEvent($order, $channelContext);
        $this->eventDispatcher->dispatch($event);

        return $channelContext;
    }

    /**
     * @param string[] $ruleIds
     *
     * @return array<string, string[]>
     */
    private function fetchRuleAreas(array $ruleIds, Context $context): array
    {
        if (!$ruleIds) {
            return [];
        }

        $criteria = new Criteria($ruleIds);
        $rules = $this->ruleRepository->search($criteria, $context)->getEntities();

        return $rules->getIdsByArea();
    }
}

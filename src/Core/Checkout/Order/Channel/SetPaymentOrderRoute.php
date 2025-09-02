<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Channel;

use HeyFrame\Core\Checkout\Cart\CartBehavior;
use HeyFrame\Core\Checkout\Cart\CartRuleLoader;
use HeyFrame\Core\Checkout\Cart\Order\OrderConverter;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Gateway\Channel\AbstractCheckoutGatewayRoute;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use HeyFrame\Core\Checkout\Order\Event\OrderPaymentMethodChangedCriteriaEvent;
use HeyFrame\Core\Checkout\Order\Event\OrderPaymentMethodChangedEvent;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Checkout\Order\OrderException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextService;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionActions;
use HeyFrame\Core\System\StateMachine\Exception\IllegalTransitionException;
use HeyFrame\Core\System\StateMachine\Loader\InitialStateIdLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('checkout')]
class SetPaymentOrderRoute extends AbstractSetPaymentOrderRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<OrderCollection> $orderRepository
     */
    public function __construct(
        private readonly OrderService $orderService,
        private readonly EntityRepository $orderRepository,
        private readonly OrderConverter $orderConverter,
        private readonly CartRuleLoader $cartRuleLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly InitialStateIdLoader $initialStateIdLoader,
        private readonly AbstractCheckoutGatewayRoute $checkoutGatewayRoute
    ) {
    }

    public function getDecorated(): AbstractSetPaymentOrderRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/front-api/order/payment',
        name: 'front-api.order.set-payment',
        defaults: ['_loginRequired' => true, '_loginRequiredAllowGuest' => true],
        methods: ['POST'],
    )]
    public function setPayment(Request $request, ChannelContext $context): SetPaymentOrderRouteResponse
    {
        $paymentMethodId = $request->request->getAlnum('paymentMethodId');
        if (!Uuid::isValid($paymentMethodId)) {
            throw OrderException::invalidUuid($paymentMethodId);
        }

        $orderId = $request->request->getAlnum('orderId');
        if (!Uuid::isValid($orderId)) {
            throw OrderException::invalidUuid($orderId);
        }

        $order = $this->loadOrder($orderId, $context);

        $context = $this->orderConverter->assembleChannelContext(
            $order,
            $context->getContext(),
            [ChannelContextService::PAYMENT_METHOD_ID => $paymentMethodId]
        );

        $this->validateRequest($request, $order, $context);

        $this->validatePaymentState($order);

        $this->setPaymentMethod($paymentMethodId, $order, $context);

        return new SetPaymentOrderRouteResponse();
    }

    private function setPaymentMethod(string $paymentMethodId, OrderEntity $order, ChannelContext $channelContext): void
    {
        $context = $channelContext->getContext();

        if ($this->tryTransition($order, $paymentMethodId, $context)) {
            return;
        }

        $initialState = $this->initialStateIdLoader->get(OrderTransactionStates::STATE_MACHINE);

        $transactionAmount = new CalculatedPrice(
            $order->getPrice()->getTotalPrice(),
            $order->getPrice()->getTotalPrice(),
            $order->getPrice()->getCalculatedTaxes(),
            $order->getPrice()->getTaxRules()
        );

        $transactionId = Uuid::randomHex();
        $payload = [
            'id' => $order->getId(),
            'primaryOrderTransactionId' => $transactionId,
            'transactions' => [
                [
                    'id' => $transactionId,
                    'paymentMethodId' => $paymentMethodId,
                    'stateId' => $initialState,
                    'amount' => $transactionAmount,
                ],
            ],
            'ruleIds' => $this->getOrderRules($order, $channelContext),
        ];

        $context->scope(
            Context::SYSTEM_SCOPE,
            function () use ($payload, $context): void {
                $this->orderRepository->update([$payload], $context);
            }
        );

        $changedOrder = $this->loadOrder($order->getId(), $channelContext);
        $transactions = $changedOrder->getTransactions();
        if ($transactions === null || ($transaction = $transactions->get($transactionId)) === null) {
            throw OrderException::orderTransactionNotFound($transactionId);
        }

        $event = new OrderPaymentMethodChangedEvent(
            $changedOrder,
            $transaction,
            $context,
            $channelContext->getChannelId()
        );
        $this->eventDispatcher->dispatch($event);
    }

    private function validateRequest(Request $request, OrderEntity $order, ChannelContext $channelContext): void
    {
        $paymentMethodId = $request->request->getAlnum('paymentMethodId');
        $cart = $this->orderConverter->convertToCart($order, $channelContext->getContext());
        $response = $this->checkoutGatewayRoute->load($request, $cart, $channelContext);

        if ($response->getPaymentMethods()->get($paymentMethodId) === null) {
            throw OrderException::paymentMethodNotAvailable($paymentMethodId);
        }
    }

    private function tryTransition(OrderEntity $order, string $paymentMethodId, Context $context): bool
    {
        $transactions = $order->getTransactions();
        if ($transactions === null || $transactions->count() < 1) {
            return false;
        }

        $lastTransaction = $order->getPrimaryOrderTransaction();

        if (!Feature::isActive('v6.8.0.0')) {
            $lastTransaction = $transactions->last();
        }

        if ($lastTransaction === null) {
            return false;
        }

        foreach ($transactions as $transaction) {
            if ($transaction->getPaymentMethodId() === $paymentMethodId && $lastTransaction->getId() === $transaction->getId()) {
                $initialState = $this->initialStateIdLoader->get(OrderTransactionStates::STATE_MACHINE);
                if ($transaction->getStateId() === $initialState) {
                    return true;
                }

                try {
                    $this->orderService->orderTransactionStateTransition(
                        $transaction->getId(),
                        StateMachineTransitionActions::ACTION_REOPEN,
                        new ParameterBag(),
                        $context
                    );

                    return true;
                } catch (IllegalTransitionException) {
                    // if we can't reopen the last transaction with a matching payment method
                    // we have to create a new transaction and cancel the previous one
                }
            }

            if ($transaction->getStateMachineState() !== null
                && \in_array($transaction->getStateMachineState()->getTechnicalName(), [OrderTransactionStates::STATE_CANCELLED, OrderTransactionStates::STATE_FAILED], true)
            ) {
                continue;
            }

            $context->scope(
                Context::SYSTEM_SCOPE,
                function () use ($transaction, $context): void {
                    $this->orderService->orderTransactionStateTransition(
                        $transaction->getId(),
                        StateMachineTransitionActions::ACTION_CANCEL,
                        new ParameterBag(),
                        $context
                    );
                }
            );
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function getOrderRules(OrderEntity $order, ChannelContext $channelContext): array
    {
        $convertedCart = $this->orderConverter->convertToCart($order, $channelContext->getContext());
        $ruleIds = $this->cartRuleLoader->loadByCart(
            $channelContext,
            $convertedCart,
            new CartBehavior($channelContext->getPermissions())
        )->getMatchingRules()->getIds();

        return array_values($ruleIds);
    }

    private function loadOrder(string $orderId, ChannelContext $context): OrderEntity
    {
        $criteria = (new Criteria([$orderId]))
            ->addAssociation('transactions')
            ->addAssociation('primaryOrderTransaction.stateMachineState');

        $criteria->getAssociation('transactions')
            ->addSorting(new FieldSorting('createdAt'));

        $customer = $context->getCustomer();
        \assert($customer !== null);

        $criteria
            ->addFilter(new EqualsFilter('order.orderCustomer.customerId', $customer->getId()))
            ->addAssociations([
                'lineItems',
                'deliveries.shippingOrderAddress',
                'deliveries.stateMachineState',
                'orderCustomer',
                'tags',
                'transactions.stateMachineState',
                'stateMachineState',
            ]);

        $this->eventDispatcher->dispatch(new OrderPaymentMethodChangedCriteriaEvent($orderId, $criteria, $context));

        $order = $this->orderRepository->search($criteria, $context->getContext())->first();
        if ($order === null) {
            throw OrderException::orderNotFound($orderId);
        }

        return $order;
    }

    /**
     * @throws OrderException
     */
    private function validatePaymentState(OrderEntity $order): void
    {
        if ($this->orderService->isPaymentChangeableByTransactionState($order)) {
            return;
        }

        throw OrderException::paymentMethodNotChangeable();
    }
}

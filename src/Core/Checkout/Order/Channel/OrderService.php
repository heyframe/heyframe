<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Channel;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\Channel\CartService;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use HeyFrame\Core\Checkout\Order\Exception\PaymentMethodNotAvailableException;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Checkout\Order\OrderException;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Content\Product\State;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\BuildValidationEvent;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\Framework\Validation\DataValidationFactoryInterface;
use HeyFrame\Core\Framework\Validation\DataValidator;
use HeyFrame\Core\Framework\Validation\Exception\ConstraintViolationException;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use HeyFrame\Core\System\StateMachine\StateMachineException;
use HeyFrame\Core\System\StateMachine\StateMachineRegistry;
use HeyFrame\Core\System\StateMachine\Transition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Package('checkout')]
class OrderService
{
    final public const CUSTOMER_COMMENT_KEY = 'customerComment';
    final public const AFFILIATE_CODE_KEY = 'affiliateCode';
    final public const CAMPAIGN_CODE_KEY = 'campaignCode';

    final public const ALLOWED_TRANSACTION_STATES = [
        OrderTransactionStates::STATE_OPEN,
        OrderTransactionStates::STATE_CANCELLED,
        OrderTransactionStates::STATE_REMINDED,
        OrderTransactionStates::STATE_FAILED,
        OrderTransactionStates::STATE_CHARGEBACK,
        OrderTransactionStates::STATE_UNCONFIRMED,
    ];

    /**
     * @internal
     *
     * @param EntityRepository<PaymentMethodCollection> $paymentMethodRepository
     */
    public function __construct(
        private readonly DataValidator $dataValidator,
        private readonly DataValidationFactoryInterface $orderValidationFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly CartService $cartService,
        private readonly EntityRepository $paymentMethodRepository,
        private readonly StateMachineRegistry $stateMachineRegistry,
    ) {
    }

    /**
     * @throws ConstraintViolationException
     */
    public function createOrder(DataBag $data, ChannelContext $context): string
    {
        $cart = $this->cartService->getCart($context->getToken(), $context);

        $this->validateOrderData($data, $context, $cart->getLineItems()->hasLineItemWithState(State::IS_DOWNLOAD));

        $this->validateCart($cart, $context->getContext());

        return $this->cartService->order($cart, $context, $data->toRequestDataBag());
    }

    /**
     * @internal Should not be called from outside the core
     */
    public function orderStateTransition(
        string $orderId,
        string $transition,
        ParameterBag $data,
        Context $context
    ): StateMachineStateEntity {
        $stateFieldName = $data->get('stateFieldName', 'stateId');

        $stateMachineStates = $this->stateMachineRegistry->transition(
            new Transition(
                'order',
                $orderId,
                $transition,
                $stateFieldName
            ),
            $context
        );

        $toPlace = $stateMachineStates->get('toPlace');

        if (!$toPlace) {
            // @deprecated tag:v6.8.0 - remove this if block
            if (!Feature::isActive('v6.8.0.0')) {
                throw StateMachineException::stateMachineStateNotFound('order', $transition); // @phpstan-ignore heyframe.domainException
            }
            throw OrderException::stateMachineStateNotFound('order', $transition);
        }

        return $toPlace;
    }

    /**
     * @internal Should not be called from outside the core
     */
    public function orderTransactionStateTransition(
        string $orderTransactionId,
        string $transition,
        ParameterBag $data,
        Context $context
    ): StateMachineStateEntity {
        $stateFieldName = $data->get('stateFieldName', 'stateId');

        $stateMachineStates = $this->stateMachineRegistry->transition(
            new Transition(
                'order_transaction',
                $orderTransactionId,
                $transition,
                $stateFieldName
            ),
            $context
        );

        $toPlace = $stateMachineStates->get('toPlace');

        if (!$toPlace) {
            // @deprecated tag:v6.8.0 - remove this if block
            if (!Feature::isActive('v6.8.0.0')) {
                throw StateMachineException::stateMachineStateNotFound('order_transaction', $transition); // @phpstan-ignore heyframe.domainException
            }
            throw OrderException::stateMachineStateNotFound('order_transaction', $transition);
        }

        return $toPlace;
    }

    /**
     * @internal Should not be called from outside the core
     */
    public function orderDeliveryStateTransition(
        string $orderDeliveryId,
        string $transition,
        ParameterBag $data,
        Context $context
    ): StateMachineStateEntity {
        $stateFieldName = $data->get('stateFieldName', 'stateId');

        $stateMachineStates = $this->stateMachineRegistry->transition(
            new Transition(
                'order_delivery',
                $orderDeliveryId,
                $transition,
                $stateFieldName
            ),
            $context
        );

        $toPlace = $stateMachineStates->get('toPlace');

        if (!$toPlace) {
            // @deprecated tag:v6.8.0 - remove this if block
            if (!Feature::isActive('v6.8.0.0')) {
                throw StateMachineException::stateMachineStateNotFound('order_delivery', $transition); // @phpstan-ignore heyframe.domainException
            }
            throw OrderException::stateMachineStateNotFound('order_delivery', $transition);
        }

        return $toPlace;
    }

    public function isPaymentChangeableByTransactionState(OrderEntity $order): bool
    {
        $state = $order->getPrimaryOrderTransaction()?->getStateMachineState()?->getTechnicalName();

        if (!Feature::isActive('v6.8.0.0')) {
            $state = $order->getTransactions()?->last()?->getStateMachineState()?->getTechnicalName();
        }

        if (!$state) {
            return true;
        }

        if (\in_array($state, self::ALLOWED_TRANSACTION_STATES, true)) {
            return true;
        }

        return false;
    }

    private function validateCart(Cart $cart, Context $context): void
    {
        $idsOfPaymentMethods = [];

        foreach ($cart->getTransactions() as $paymentMethod) {
            $idsOfPaymentMethods[] = $paymentMethod->getPaymentMethodId();
        }

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('active', true)
        );

        $paymentMethods = $this->paymentMethodRepository->searchIds($criteria, $context);

        if ($paymentMethods->getTotal() !== \count(array_unique($idsOfPaymentMethods))) {
            foreach ($cart->getTransactions() as $paymentMethod) {
                if (!\in_array($paymentMethod->getPaymentMethodId(), $paymentMethods->getIds(), true)) {
                    // @deprecated tag:v6.8.0 - remove this if block
                    if (!Feature::isActive('v6.8.0.0')) {
                        throw new PaymentMethodNotAvailableException($paymentMethod->getPaymentMethodId()); // @phpstan-ignore heyframe.domainException
                    }
                    throw OrderException::paymentMethodNotAvailable($paymentMethod->getPaymentMethodId());
                }
            }
        }
    }

    /**
     * @throws ConstraintViolationException
     */
    private function validateOrderData(
        ParameterBag $data,
        ChannelContext $context,
        bool $hasVirtualGoods
    ): void {
        $definition = $this->getOrderCreateValidationDefinition(new DataBag($data->all()), $context, $hasVirtualGoods);
        $violations = $this->dataValidator->getViolations($data->all(), $definition);

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, $data->all());
        }
    }

    private function getOrderCreateValidationDefinition(
        DataBag $data,
        ChannelContext $context,
        bool $hasVirtualGoods
    ): DataValidationDefinition {
        $validation = $this->orderValidationFactory->create($context);

        if ($hasVirtualGoods) {
            $validation->add('revocation', new NotBlank());
        }

        $validationEvent = new BuildValidationEvent($validation, $data, $context->getContext());
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());

        return $validation;
    }
}

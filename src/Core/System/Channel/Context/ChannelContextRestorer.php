<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Context;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use HeyFrame\Core\Checkout\Cart\CartBehavior;
use HeyFrame\Core\Checkout\Cart\CartRuleLoader;
use HeyFrame\Core\Checkout\Cart\Order\OrderConverter;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ChannelException;
use HeyFrame\Core\System\Channel\Event\ChannelContextRestorerOrderCriteriaEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('framework')]
class ChannelContextRestorer
{
    /**
     * @internal
     *
     * @param EntityRepository<OrderCollection> $orderRepository
     */
    public function __construct(
        private readonly AbstractChannelContextFactory $factory,
        private readonly CartRuleLoader $cartRuleLoader,
        private readonly OrderConverter $orderConverter,
        private readonly EntityRepository $orderRepository,
        private readonly Connection $connection,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param array<string, string|array<string,bool>|null> $overrideOptions
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function restoreByOrder(string $orderId, Context $context, array $overrideOptions = []): ChannelContext
    {
        $order = $this->getOrderById($orderId, $context);
        if ($order === null) {
            throw ChannelException::orderNotFound($orderId);
        }

        if ($order->getOrderCustomer() === null) {
            throw ChannelException::missingAssociation('orderCustomer');
        }

        $customer = $order->getOrderCustomer()->getCustomer();
        $customerGroupId = null;
        if ($customer) {
            $customerGroupId = $customer->getGroupId();
        }

        $billingAddress = $order->getBillingAddress();
        $countryStateId = null;
        if ($billingAddress) {
            $countryStateId = $billingAddress->getCountryStateId();
        }

        $options = [
            ChannelContextService::CURRENCY_ID => $order->getCurrencyId(),
            ChannelContextService::LANGUAGE_ID => $order->getLanguageId(),
            ChannelContextService::CUSTOMER_ID => $order->getOrderCustomer()->getCustomerId(),
            ChannelContextService::COUNTRY_STATE_ID => $countryStateId,
            ChannelContextService::CUSTOMER_GROUP_ID => $customerGroupId,
            ChannelContextService::PERMISSIONS => OrderConverter::ADMIN_EDIT_ORDER_PERMISSIONS,
            ChannelContextService::VERSION_ID => $context->getVersionId(),
        ];

        if ($paymentMethodId = $this->getPaymentMethodId($order)) {
            $options[ChannelContextService::PAYMENT_METHOD_ID] = $paymentMethodId;
        }

        $shippingMethodId = $order->getPrimaryOrderDelivery()?->getShippingMethodId();

        if (!Feature::isActive('v6.8.0.0')) {
            $shippingMethodId = $order->getDeliveries()?->first()?->getShippingMethodId();
        }

        if ($shippingMethodId !== null) {
            $options[ChannelContextService::SHIPPING_METHOD_ID] = $shippingMethodId;
        }

        $options = array_merge($options, $overrideOptions);

        $channelContext = $this->factory->create(
            Uuid::randomHex(),
            $order->getChannelId(),
            $options
        );

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

        $cart = $this->orderConverter->convertToCart($order, $channelContext->getContext());
        $this->cartRuleLoader->loadByCart(
            $channelContext,
            $cart,
            new CartBehavior($channelContext->getPermissions()),
            true
        );

        return $channelContext;
    }

    /**
     * @param array<string> $overrideOptions
     *
     * @throws Exception
     */
    public function restoreByCustomer(string $customerId, Context $context, array $overrideOptions = []): ChannelContext
    {
        $customer = $this->connection->createQueryBuilder()
            ->select(
                'LOWER(HEX(language_id))',
                'LOWER(HEX(customer_group_id))',
                'LOWER(HEX(channel_id))',
            )
            ->from('customer')
            ->where('id = :id')
            ->setParameter('id', Uuid::fromHexToBytes($customerId))
            ->executeQuery()
            ->fetchAssociative();

        if (!$customer) {
            throw ChannelException::customerNotFoundByIdException($customerId);
        }

        [$languageId, $groupId, $channelId] = array_values($customer);
        $options = [
            ChannelContextService::LANGUAGE_ID => $languageId,
            ChannelContextService::CUSTOMER_ID => $customerId,
            ChannelContextService::CUSTOMER_GROUP_ID => $groupId,
            ChannelContextService::VERSION_ID => $context->getVersionId(),
        ];

        $options = array_merge($options, $overrideOptions);

        $token = Uuid::randomHex();
        $channelContext = $this->factory->create(
            $token,
            $channelId,
            $options
        );

        $this->cartRuleLoader->loadByToken($channelContext, $token);
        $channelContext->getContext()->addState(...$context->getStates());

        return $channelContext;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function getOrderById(string $orderId, Context $context): ?OrderEntity
    {
        $criteria = (new Criteria([$orderId]))
            ->addAssociation('primaryOrderTransaction')
            ->addAssociation('primaryOrderDelivery')
            ->addAssociation('lineItems')
            ->addAssociation('currency')
            ->addAssociation('deliveries')
            ->addAssociation('language.locale')
            ->addAssociation('orderCustomer.customer')
            ->addAssociation('billingAddress')
            ->addAssociation('transactions');

        $this->eventDispatcher->dispatch(new ChannelContextRestorerOrderCriteriaEvent($criteria, $context));

        return $this->orderRepository->search($criteria, $context)->getEntities()->get($orderId);
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function getPaymentMethodId(OrderEntity $order): ?string
    {
        $transactions = $order->getTransactions();
        if ($transactions === null) {
            throw ChannelException::missingAssociation('transactions');
        }

        foreach ($transactions as $transaction) {
            if ($transaction->getStateMachineState() !== null
                && ($transaction->getStateMachineState()->getTechnicalName() === OrderTransactionStates::STATE_CANCELLED
                    || $transaction->getStateMachineState()->getTechnicalName() === OrderTransactionStates::STATE_FAILED)
            ) {
                continue;
            }

            return $transaction->getPaymentMethodId();
        }

        if (!Feature::isActive('v6.8.0.0')) {
            return $transactions->last() ? $transactions->last()->getPaymentMethodId() : null;
        }

        return $order->getPrimaryOrderTransaction()?->getPaymentMethodId();
    }
}

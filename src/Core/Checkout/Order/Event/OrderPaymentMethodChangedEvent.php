<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Event;

use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Checkout\Order\OrderException;
use HeyFrame\Core\Content\Flow\Dispatching\Aware\OrderTransactionAware;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\ChannelAware;
use HeyFrame\Core\Framework\Event\CustomerAware;
use HeyFrame\Core\Framework\Event\EventData\EntityType;
use HeyFrame\Core\Framework\Event\EventData\EventDataCollection;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Event\OrderAware;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class OrderPaymentMethodChangedEvent extends Event implements ChannelAware, OrderAware, CustomerAware, OrderTransactionAware, FlowEventAware
{
    final public const EVENT_NAME = 'checkout.order.payment_method.changed';

    public function __construct(
        private readonly OrderEntity $order,
        private readonly OrderTransactionEntity $orderTransaction,
        private readonly Context $context,
        private readonly string $channelId
    ) {
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    public function getOrderTransaction(): OrderTransactionEntity
    {
        return $this->orderTransaction;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function getOrderId(): string
    {
        return $this->order->getId();
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('order', new EntityType(OrderDefinition::class))
            ->add('orderTransaction', new EntityType(OrderTransactionDefinition::class));
    }

    public function getCustomerId(): string
    {
        $orderCustomer = $this->order->getOrderCustomer();

        if (!$orderCustomer?->getCustomerId()) {
            throw OrderException::orderCustomerDeleted($this->order->getId());
        }

        return $orderCustomer->getCustomerId();
    }

    public function getOrderTransactionId(): string
    {
        return $this->orderTransaction->getId();
    }
}

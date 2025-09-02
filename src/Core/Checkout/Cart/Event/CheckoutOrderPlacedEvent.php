<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Event;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\ChannelAware;
use HeyFrame\Core\Framework\Event\CustomerAware;
use HeyFrame\Core\Framework\Event\CustomerGroupAware;
use HeyFrame\Core\Framework\Event\EventData\EntityType;
use HeyFrame\Core\Framework\Event\EventData\EventDataCollection;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Event\OrderAware;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\Awareness\ChannelContextAware;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class CheckoutOrderPlacedEvent extends Event implements ChannelAware, ChannelContextAware, OrderAware, CustomerAware, CustomerGroupAware, FlowEventAware
{
    final public const EVENT_NAME = 'checkout.order.placed';

    public function __construct(
        private readonly ChannelContext $context,
        private readonly OrderEntity $order,
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

    public function getOrderId(): string
    {
        return $this->order->getId();
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('order', new EntityType(OrderDefinition::class));
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->context;
    }

    public function getChannelId(): string
    {
        return $this->context->getChannelId();
    }

    public function getCustomerId(): string
    {
        $customerId = $this->order->getOrderCustomer()?->getCustomerId();

        if (!$customerId) {
            throw CartException::orderCustomerDeleted($this->order->getId());
        }

        return $customerId;
    }

    public function getCustomerGroupId(): string
    {
        return $this->context->getCustomerGroupId();
    }
}

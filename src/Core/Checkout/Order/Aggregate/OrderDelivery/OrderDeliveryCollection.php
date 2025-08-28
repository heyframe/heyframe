<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Aggregate\OrderDelivery;

use HeyFrame\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressCollection;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<OrderDeliveryEntity>
 */
#[Package('checkout')]
class OrderDeliveryCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getOrderIds(): array
    {
        return $this->fmap(fn (OrderDeliveryEntity $orderDelivery) => $orderDelivery->getOrderId());
    }

    public function filterByOrderId(string $id): self
    {
        return $this->filter(fn (OrderDeliveryEntity $orderDelivery) => $orderDelivery->getOrderId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getShippingAddressIds(): array
    {
        return $this->fmap(fn (OrderDeliveryEntity $orderDelivery) => $orderDelivery->getShippingOrderAddressId());
    }

    public function filterByShippingAddressId(string $id): self
    {
        return $this->filter(fn (OrderDeliveryEntity $orderDelivery) => $orderDelivery->getShippingOrderAddressId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getShippingMethodIds(): array
    {
        return $this->fmap(fn (OrderDeliveryEntity $orderDelivery) => $orderDelivery->getShippingMethodId());
    }

    public function filterByShippingMethodId(string $id): self
    {
        return $this->filter(fn (OrderDeliveryEntity $orderDelivery) => $orderDelivery->getShippingMethodId() === $id);
    }

    public function getShippingAddress(): OrderAddressCollection
    {
        return new OrderAddressCollection(
            $this->fmap(fn (OrderDeliveryEntity $orderDelivery) => $orderDelivery->getShippingOrderAddress())
        );
    }

    public function getShippingMethods(): ShippingMethodCollection
    {
        return new ShippingMethodCollection(
            $this->fmap(fn (OrderDeliveryEntity $orderDelivery) => $orderDelivery->getShippingMethod())
        );
    }

    public function getApiAlias(): string
    {
        return 'order_delivery_collection';
    }

    protected function getExpectedClass(): string
    {
        return OrderDeliveryEntity::class;
    }
}

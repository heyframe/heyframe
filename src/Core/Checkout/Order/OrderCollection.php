<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order;

use HeyFrame\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Core\System\Currency\CurrencyCollection;

/**
 * @extends EntityCollection<OrderEntity>
 */
#[Package('checkout')]
class OrderCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getCurrencyIds(): array
    {
        return $this->fmap(fn (OrderEntity $order) => $order->getCurrencyId());
    }

    public function filterByCurrencyId(string $id): self
    {
        return $this->filter(fn (OrderEntity $order) => $order->getCurrencyId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getChannelIs(): array
    {
        return $this->fmap(fn (OrderEntity $order) => $order->getChannelId());
    }

    public function filterByChannelId(string $id): self
    {
        return $this->filter(fn (OrderEntity $order) => $order->getChannelId() === $id);
    }

    public function getOrderCustomers(): OrderCustomerCollection
    {
        return new OrderCustomerCollection(
            $this->fmap(fn (OrderEntity $order) => $order->getOrderCustomer())
        );
    }

    public function getCurrencies(): CurrencyCollection
    {
        return new CurrencyCollection(
            $this->fmap(fn (OrderEntity $order) => $order->getCurrency())
        );
    }

    public function getChannels(): ChannelCollection
    {
        return new ChannelCollection(
            $this->fmap(fn (OrderEntity $order) => $order->getChannel())
        );
    }

    public function getApiAlias(): string
    {
        return 'order_collection';
    }

    protected function getExpectedClass(): string
    {
        return OrderEntity::class;
    }
}

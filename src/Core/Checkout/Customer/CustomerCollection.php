<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupCollection;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelCollection;

/**
 * @extends EntityCollection<CustomerEntity>
 */
#[Package('checkout')]
class CustomerCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getGroupIds(): array
    {
        return $this->fmap(fn (CustomerEntity $customer) => $customer->getGroupId());
    }

    public function filterByGroupId(string $id): self
    {
        return $this->filter(fn (CustomerEntity $customer) => $customer->getGroupId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getChannelIds(): array
    {
        return $this->fmap(fn (CustomerEntity $customer) => $customer->getChannelId());
    }

    public function filterByChannelId(string $id): self
    {
        return $this->filter(fn (CustomerEntity $customer) => $customer->getChannelId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getLastPaymentMethodIds(): array
    {
        return $this->fmap(fn (CustomerEntity $customer) => $customer->getLastPaymentMethodId());
    }

    public function filterByLastPaymentMethodId(string $id): self
    {
        return $this->filter(fn (CustomerEntity $customer) => $customer->getLastPaymentMethodId() === $id);
    }

    public function getGroups(): CustomerGroupCollection
    {
        return new CustomerGroupCollection(
            $this->fmap(fn (CustomerEntity $customer) => $customer->getGroup())
        );
    }

    public function getChannels(): ChannelCollection
    {
        return new ChannelCollection(
            $this->fmap(fn (CustomerEntity $customer) => $customer->getChannel())
        );
    }

    public function getLastPaymentMethods(): PaymentMethodCollection
    {
        return new PaymentMethodCollection(
            $this->fmap(fn (CustomerEntity $customer) => $customer->getLastPaymentMethod())
        );
    }

    public function getApiAlias(): string
    {
        return 'customer_collection';
    }

    protected function getExpectedClass(): string
    {
        return CustomerEntity::class;
    }
}

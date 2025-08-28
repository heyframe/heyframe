<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<CustomerEntity>
 */
class CustomerCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'member_collection';
    }

    protected function getExpectedClass(): string
    {
        return CustomerEntity::class;
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<CustomerGroupEntity>
 */
class CustomerGroupCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'customer_group_collection';
    }

    protected function getExpectedClass(): string
    {
        return CustomerGroupEntity::class;
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Aggregate\CustomerMemberships;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<CustomerMembershipsEntity>
 */
#[Package('discovery')]
class CustomerMembershipsCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'customer_memberships_collection';
    }

    protected function getExpectedClass(): string
    {
        return CustomerMembershipsEntity::class;
    }
}

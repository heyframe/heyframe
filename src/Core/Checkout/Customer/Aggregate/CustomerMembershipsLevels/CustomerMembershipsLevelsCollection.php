<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Aggregate\CustomerMembershipsLevels;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<CustomerMembershipsLevelsEntity>
 */
#[Package('discovery')]
class CustomerMembershipsLevelsCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'customer_memberships_levels_collection';
    }

    protected function getExpectedClass(): string
    {
        return CustomerMembershipsLevelsEntity::class;
    }
}

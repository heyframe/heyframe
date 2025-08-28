<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Aggregate\CustomerRecovery;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<CustomerRecoveryEntity>
 */
#[Package('checkout')]
class CustomerRecoveryCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'customer_recovery_collection';
    }

    protected function getExpectedClass(): string
    {
        return CustomerRecoveryEntity::class;
    }
}

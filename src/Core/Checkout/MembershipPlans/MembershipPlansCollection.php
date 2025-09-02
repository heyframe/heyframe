<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\MembershipPlans;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<MembershipPlansEntity>
 */
#[Package('after-sales')]
class MembershipPlansCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'membership_plans';
    }

    protected function getExpectedClass(): string
    {
        return MembershipPlansEntity::class;
    }
}

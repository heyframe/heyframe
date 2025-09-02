<?php declare(strict_types=1);

namespace HeyFrame\Core\System\MembershipLevels;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<MembershipLevelsEntity>
 */
#[Package('discovery')]
class MembershipLevelsCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'membership_levels_collection';
    }

    protected function getExpectedClass(): string
    {
        return MembershipLevelsEntity::class;
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\System\User\Aggregate\UserRecovery;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<UserRecoveryEntity>
 */
#[Package('fundamentals@framework')]
class UserRecoveryCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'user_recovery_collection';
    }

    protected function getExpectedClass(): string
    {
        return UserRecoveryEntity::class;
    }
}

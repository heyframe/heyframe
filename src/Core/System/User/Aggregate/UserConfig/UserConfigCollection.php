<?php declare(strict_types=1);

namespace HeyFrame\Core\System\User\Aggregate\UserConfig;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<UserConfigEntity>
 */
#[Package('fundamentals@framework')]
class UserConfigCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'user_config_collection';
    }

    protected function getExpectedClass(): string
    {
        return UserConfigEntity::class;
    }
}

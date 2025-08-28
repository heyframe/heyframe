<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Acl\Role;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<AclRoleEntity>
 */
#[Package('framework')]
class AclRoleCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dal_acl_role_collection';
    }

    protected function getExpectedClass(): string
    {
        return AclRoleEntity::class;
    }
}

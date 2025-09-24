<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Acl\Front\Role;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<CustomerRoleEntity>
 */
#[Package('framework')]
class CustomerRoleCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dal_customer_role_collection';
    }

    protected function getExpectedClass(): string
    {
        return CustomerRoleEntity::class;
    }
}

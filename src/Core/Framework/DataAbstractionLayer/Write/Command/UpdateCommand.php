<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command;

use HeyFrame\Core\Framework\Api\Acl\Admin\Role\AclRoleDefinition;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @phpstan-ignore-next-line cannot be final, as it is extended, also designed to be used directly
 */
#[Package('framework')]
class UpdateCommand extends WriteCommand implements ChangeSetAware
{
    use ChangeSetAwareTrait;

    public function getPrivilege(): ?string
    {
        return AclRoleDefinition::PRIVILEGE_UPDATE;
    }
}

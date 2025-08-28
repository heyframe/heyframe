<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command;

use HeyFrame\Core\Framework\Api\Acl\Role\AclRoleDefinition;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('framework')]
class InsertCommand extends WriteCommand
{
    public function getPrivilege(): string
    {
        return AclRoleDefinition::PRIVILEGE_CREATE;
    }
}

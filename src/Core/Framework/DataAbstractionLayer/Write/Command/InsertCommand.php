<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command;

use HeyFrame\Core\Framework\Api\Acl\Role\AclRoleDefinition;

/**
 * @final
 */
class InsertCommand extends WriteCommand
{
    public function getPrivilege(): string
    {
        return AclRoleDefinition::PRIVILEGE_CREATE;
    }
}

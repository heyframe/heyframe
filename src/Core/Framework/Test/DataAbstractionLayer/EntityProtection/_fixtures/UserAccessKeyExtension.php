<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\DataAbstractionLayer\EntityProtection\_fixtures;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtension;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityProtection\EntityProtectionCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityProtection\ReadProtection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityProtection\WriteProtection;
use HeyFrame\Core\System\User\Aggregate\UserAccessKey\UserAccessKeyDefinition;

/**
 * @internal
 */
class UserAccessKeyExtension extends EntityExtension
{
    public function extendProtections(EntityProtectionCollection $protections): void
    {
        $protections->add(new ReadProtection(Context::SYSTEM_SCOPE, Context::USER_SCOPE));
        $protections->add(new WriteProtection(Context::SYSTEM_SCOPE, Context::USER_SCOPE));
    }

    public function getEntityName(): string
    {
        return UserAccessKeyDefinition::ENTITY_NAME;
    }
}

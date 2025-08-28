<?php declare(strict_types=1);

namespace HeyFrame\Core\Service;

use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
enum State: string
{
    case ACTIVE = 'active';

    case PENDING_PERMISSIONS = 'pending_permissions';

    case INACTIVE = 'inactive';

    public static function state(AppEntity $appEntity): State
    {
        if (\count($appEntity->getRequestedPrivileges()) === 0 && $appEntity->isActive()) {
            return State::ACTIVE;
        }

        if (\count($appEntity->getRequestedPrivileges()) > 0 && $appEntity->isActive()) {
            return State::PENDING_PERMISSIONS;
        }

        return State::INACTIVE;
    }
}

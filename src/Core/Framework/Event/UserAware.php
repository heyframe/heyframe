<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
#[IsFlowEventAware]
interface UserAware
{
    public const USER_RECOVERY = 'userRecovery';

    public const USER_RECOVERY_ID = 'userRecoveryId';

    public function getUserId(): string;
}

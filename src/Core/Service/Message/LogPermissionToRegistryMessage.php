<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\Message;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\AsyncMessageInterface;
use HeyFrame\Core\Service\Permission\ConsentState;
use HeyFrame\Core\Service\Permission\PermissionsConsent;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class LogPermissionToRegistryMessage implements AsyncMessageInterface
{
    public function __construct(public readonly PermissionsConsent $permissionsConsent, public readonly ConsentState $consentState)
    {
    }
}

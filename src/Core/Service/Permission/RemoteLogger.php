<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\Permission;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
interface RemoteLogger
{
    public function log(PermissionsConsent $consent, ConsentState $state): void;
}

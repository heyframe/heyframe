<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\ServiceRegistry;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
readonly class ServiceEntry
{
    public function __construct(public string $name, public string $description, public string $host, public string $appEndpoint, public bool $activateOnInstall = true, public ?string $licenseSyncEndPoint = null)
    {
    }
}

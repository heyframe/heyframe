<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Webhook;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('framework')]
class AclPrivilegeCollection
{
    /**
     * @param array<string> $privileges
     */
    public function __construct(private readonly array $privileges)
    {
    }

    public function isAllowed(string $resource, string $privilege): bool
    {
        return \in_array($resource . ':' . $privilege, $this->privileges, true);
    }
}

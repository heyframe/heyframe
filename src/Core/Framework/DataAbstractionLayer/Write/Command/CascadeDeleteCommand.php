<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('framework')]
class CascadeDeleteCommand extends DeleteCommand
{
    public function isValid(): bool
    {
        // prevent execution
        return false;
    }

    public function getPrivilege(): ?string
    {
        return null;
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\EntityProtection;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
abstract class EntityProtection
{
    /**
     * Returns a readable name for the flag
     */
    abstract public function parse(): \Generator;

    /**
     * Can be overriden if protection is aware of different scopes
     */
    public function isAllowed(string $scope): bool
    {
        return true;
    }
}

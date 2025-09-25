<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('checkout')]
class CartBehavior extends Struct
{
    /**
     * @param array<string, bool> $permissions
     */
    public function __construct(
        private readonly array $permissions = []
    ) {
    }

    public function hasPermission(string $permission): bool
    {
        return !empty($this->permissions[$permission]);
    }

    public function getApiAlias(): string
    {
        return 'cart_behavior';
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Wallet;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<WalletEntity>
 */
#[Package('checkout')]
class WalletCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'wallet_collection';
    }

    protected function getExpectedClass(): string
    {
        return WalletEntity::class;
    }
}

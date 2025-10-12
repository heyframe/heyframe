<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Wallet\Aggregate\WalletTransactions;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<WalletTransactionsEntity>
 */
#[Package('checkout')]
class WalletTransactionsCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'wallet_transactions';
    }

    protected function getExpectedClass(): string
    {
        return WalletTransactionsEntity::class;
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Wallet;

use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class WalletEvents
{
    final public const WALLET_WRITTEN_EVENT = 'wallet.written';
    final public const WALLET_DELETED_EVENT = 'wallet.deleted';
    final public const WALLET_LOADED_EVENT = 'wallet.loaded';
}

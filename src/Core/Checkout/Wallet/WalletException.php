<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Wallet;

use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class WalletException extends HttpException
{
    public const WALLET_NOT_FOUND = 'CHECKOUT__WALLET_NOT_FOUND';
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Wallet\Cart\PaymentHandler;

use HeyFrame\Core\Checkout\Payment\Cart\PaymentHandler\DefaultPayment;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class WalletPayment extends DefaultPayment
{
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\Cart\PaymentHandler;

use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
enum PaymentHandlerType
{
    case RECURRING;
    case REFUND;
}

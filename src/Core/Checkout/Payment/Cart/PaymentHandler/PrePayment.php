<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\Cart\PaymentHandler;

use HeyFrame\Core\Checkout\Payment\Cart\PaymentTransactionStruct;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('checkout')]
class PrePayment extends DefaultPayment
{
    public function supports(PaymentHandlerType $type, string $paymentMethodId, Context $context): bool
    {
        return $type === PaymentHandlerType::RECURRING;
    }

    public function recurring(PaymentTransactionStruct $transaction, Context $context): void
    {
    }
}

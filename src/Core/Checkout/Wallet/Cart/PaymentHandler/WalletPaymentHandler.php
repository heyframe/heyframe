<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Wallet\Cart\PaymentHandler;

use HeyFrame\Core\Checkout\Payment\Cart\PaymentHandler\AbstractPaymentHandler;
use HeyFrame\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerType;
use HeyFrame\Core\Checkout\Payment\Cart\PaymentTransactionStruct;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayStruct;
use HeyFrame\Core\Framework\Struct\Struct;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
class WalletPaymentHandler extends AbstractPaymentHandler
{
    public function supports(PaymentHandlerType $type, string $paymentMethodId, Context $context): bool
    {
        return true;
    }

    public function pay(Request $request, PaymentTransactionStruct $transaction, Context $context, ?Struct $validateStruct): RedirectResponse|ArrayStruct|null
    {
        return null;
    }
}

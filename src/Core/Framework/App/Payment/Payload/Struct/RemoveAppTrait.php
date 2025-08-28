<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Payment\Payload\Struct;

use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
trait RemoveAppTrait
{
    private function removeApp(OrderTransactionEntity $orderTransaction): OrderTransactionEntity
    {
        $orderTransaction = clone $orderTransaction;
        $paymentMethod = $orderTransaction->getPaymentMethod();
        if ($paymentMethod === null) {
            return $orderTransaction;
        }
        $paymentMethod = clone $paymentMethod;
        $orderTransaction->setPaymentMethod($paymentMethod);

        $appPaymentMethod = $paymentMethod->getAppPaymentMethod();
        if ($appPaymentMethod === null) {
            return $orderTransaction;
        }

        $appPaymentMethod = clone $appPaymentMethod;
        $appPaymentMethod->setApp(null);
        $paymentMethod->setAppPaymentMethod($appPaymentMethod);

        return $orderTransaction;
    }
}

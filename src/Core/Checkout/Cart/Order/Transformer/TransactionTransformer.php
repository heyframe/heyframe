<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Order\Transformer;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Transaction\Struct\Transaction;
use HeyFrame\Core\Checkout\Cart\Transaction\Struct\TransactionCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class TransactionTransformer
{
    /**
     * @return array<int, array<string, string|CalculatedPrice|array<array-key, mixed>|null>>
     */
    public static function transformCollection(
        TransactionCollection $transactions,
        string $stateId,
        Context $context
    ): array {
        $output = [];
        foreach ($transactions as $transaction) {
            $output[] = self::transform($transaction, $stateId, $context);
        }

        return $output;
    }

    /**
     * @return array<string, string|CalculatedPrice|array<array-key, mixed>|null>
     */
    public static function transform(
        Transaction $transaction,
        string $stateId,
        Context $context
    ): array {
        return [
            'paymentMethodId' => $transaction->getPaymentMethodId(),
            'amount' => $transaction->getAmount(),
            'stateId' => $stateId,
            'validationData' => $transaction->getValidationStruct()?->jsonSerialize(),
        ];
    }
}

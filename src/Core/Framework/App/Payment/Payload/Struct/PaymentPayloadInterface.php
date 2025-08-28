<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Payment\Payload\Struct;

use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use HeyFrame\Core\Framework\App\Payload\SourcedPayloadInterface;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('checkout')]
interface PaymentPayloadInterface extends SourcedPayloadInterface
{
    public function getOrderTransaction(): OrderTransactionEntity;
}

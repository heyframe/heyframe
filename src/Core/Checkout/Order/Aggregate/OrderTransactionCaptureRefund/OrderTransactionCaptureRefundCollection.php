<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Aggregate\OrderTransactionCaptureRefund;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<OrderTransactionCaptureRefundEntity>
 */
#[Package('checkout')]
class OrderTransactionCaptureRefundCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'order_transaction_capture_refund_collection';
    }

    protected function getExpectedClass(): string
    {
        return OrderTransactionCaptureRefundEntity::class;
    }
}

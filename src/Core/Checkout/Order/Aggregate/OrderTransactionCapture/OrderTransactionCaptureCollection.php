<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Aggregate\OrderTransactionCapture;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<OrderTransactionCaptureEntity>
 */
#[Package('checkout')]
class OrderTransactionCaptureCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'order_transaction_capture_collection';
    }

    protected function getExpectedClass(): string
    {
        return OrderTransactionCaptureEntity::class;
    }
}

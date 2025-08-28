<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Aware;

use HeyFrame\Core\Framework\Event\IsFlowEventAware;

#[IsFlowEventAware]
interface OrderTransactionAware
{
    public const ORDER_TRANSACTION_ID = 'orderTransactionId';

    public const ORDER_TRANSACTION = 'orderTransaction';

    public function getOrderTransactionId(): string;
}

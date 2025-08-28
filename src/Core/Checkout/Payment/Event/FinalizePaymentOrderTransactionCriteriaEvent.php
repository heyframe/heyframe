<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\Event;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class FinalizePaymentOrderTransactionCriteriaEvent extends Event
{
    public function __construct(
        private readonly string $orderTransactionId,
        private readonly Criteria $criteria,
        private readonly ChannelContext $context
    ) {
    }

    public function getOrderTransactionId(): string
    {
        return $this->orderTransactionId;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    public function getContext(): ChannelContext
    {
        return $this->context;
    }
}

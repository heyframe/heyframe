<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class CustomerWishlistLoaderCriteriaEvent extends NestedEvent implements HeyFrameChannelEvent
{
    final public const EVENT_NAME = 'checkout.customer.customer_wishlist_loader_criteria';

    public function __construct(
        private readonly Criteria $criteria,
        private readonly ChannelContext $context
    ) {
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->context;
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Event;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class BeforeCartMergeEvent extends Event implements HeyFrameChannelEvent
{
    /**
     * @internal
     */
    public function __construct(
        protected Cart $customerCart,
        protected LineItemCollection $mergeableLineItems,
        protected ChannelContext $context
    ) {
    }

    public function getCustomerCart(): Cart
    {
        return $this->customerCart;
    }

    public function getMergeableLineItems(): LineItemCollection
    {
        return $this->mergeableLineItems;
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->context;
    }
}

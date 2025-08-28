<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Event;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class BeforeLineItemQuantityChangedEvent implements HeyFrameChannelEvent, CartEvent
{
    public function __construct(
        protected readonly LineItem $lineItem,
        protected readonly Cart $cart,
        protected readonly ChannelContext $channelContext,
        protected readonly int $beforeUpdateQuantity
    ) {
    }

    public function getLineItem(): LineItem
    {
        return $this->lineItem;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getContext(): Context
    {
        return $this->channelContext->getContext();
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function getBeforeUpdateQuantity(): int
    {
        return $this->beforeUpdateQuantity;
    }
}

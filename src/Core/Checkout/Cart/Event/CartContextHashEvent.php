<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Event;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartContextHashStruct;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class CartContextHashEvent extends Event implements HeyFrameChannelEvent, CartEvent
{
    public function __construct(
        protected readonly ChannelContext $channelContext,
        protected readonly Cart $cart,
        protected CartContextHashStruct $hashStruct
    ) {
    }

    public function getContext(): Context
    {
        return $this->channelContext->getContext();
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getHashStruct(): CartContextHashStruct
    {
        return $this->hashStruct;
    }

    public function setHashStruct(CartContextHashStruct $hashStruct): void
    {
        $this->hashStruct = $hashStruct;
    }
}

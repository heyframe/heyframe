<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Event;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class CartVerifyPersistEvent extends Event implements HeyFrameChannelEvent, CartEvent
{
    public function __construct(
        protected ChannelContext $context,
        protected Cart $cart,
        protected bool $shouldPersist
    ) {
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->context;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function shouldBePersisted(): bool
    {
        return $this->shouldPersist;
    }

    public function setShouldPersist(bool $persist): void
    {
        $this->shouldPersist = $persist;
    }
}

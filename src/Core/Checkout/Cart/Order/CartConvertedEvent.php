<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Order;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class CartConvertedEvent extends NestedEvent implements HeyFrameChannelEvent
{
    /**
     * @var array<mixed>
     */
    private array $convertedCart;

    /**
     * @param array<mixed> $originalConvertedCart
     */
    public function __construct(
        private readonly Cart $cart,
        private readonly array $originalConvertedCart,
        private readonly ChannelContext $channelContext,
        private readonly OrderConversionContext $conversionContext
    ) {
        $this->convertedCart = $originalConvertedCart;
    }

    public function getContext(): Context
    {
        return $this->channelContext->getContext();
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    /**
     * @return mixed[]
     */
    public function getOriginalConvertedCart(): array
    {
        return $this->originalConvertedCart;
    }

    /**
     * @return mixed[]
     */
    public function getConvertedCart(): array
    {
        return $this->convertedCart;
    }

    /**
     * @param mixed[] $convertedCart
     */
    public function setConvertedCart(array $convertedCart): void
    {
        $this->convertedCart = $convertedCart;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function getConversionContext(): OrderConversionContext
    {
        return $this->conversionContext;
    }
}

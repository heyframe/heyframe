<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Extension;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\Event\CartEvent;
use HeyFrame\Core\Checkout\Cart\Order\OrderPlaceResult;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Extensions\Extension;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @codeCoverageIgnore
 *
 * @extends Extension<OrderPlaceResult>
 */
#[Package('checkout')]
final class CheckoutPlaceOrderExtension extends Extension implements HeyFrameChannelEvent, CartEvent
{
    public const NAME = 'checkout.place-order';

    /**
     * @internal heyframe owns the __constructor, but the properties are public API
     */
    public function __construct(
        /**
         * @public
         *
         * @description The cart is already calculated and can be processed to place the order
         */
        public readonly Cart $cart,
        /**
         * @public
         *
         * @description Contains the current customer session parameters
         */
        public readonly ChannelContext $context,
        /**
         * @public
         *
         * @description Contains additional request parameters like customer comments etc.
         */
        public readonly RequestDataBag $data
    ) {
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->context;
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }
}

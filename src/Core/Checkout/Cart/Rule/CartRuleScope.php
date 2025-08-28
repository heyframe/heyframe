<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\CheckoutRuleScope;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('fundamentals@after-sales')]
class CartRuleScope extends CheckoutRuleScope
{
    public function __construct(
        protected Cart $cart,
        ChannelContext $context
    ) {
        parent::__construct($context);
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }
}

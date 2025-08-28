<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Rule;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\Rule\CartRuleScope;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('after-sales')]
class FlowRuleScope extends CartRuleScope
{
    public function __construct(
        private readonly OrderEntity $order,
        Cart $cart,
        ChannelContext $context
    ) {
        parent::__construct($cart, $context);
    }

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }
}

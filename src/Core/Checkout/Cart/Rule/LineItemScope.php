<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\CheckoutRuleScope;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('fundamentals@after-sales')]
class LineItemScope extends CheckoutRuleScope
{
    public function __construct(
        protected LineItem $lineItem,
        ChannelContext $context
    ) {
        parent::__construct($context);
    }

    public function getLineItem(): LineItem
    {
        return $this->lineItem;
    }
}

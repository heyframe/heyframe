<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\RuleScope;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class CheckoutRuleScope extends RuleScope
{
    public function __construct(
        protected ChannelContext $context
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
}

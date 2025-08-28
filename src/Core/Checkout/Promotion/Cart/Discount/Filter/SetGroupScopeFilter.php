<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Cart\Discount\Filter;

use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountLineItem;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
abstract class SetGroupScopeFilter
{
    abstract public function getDecorated(): SetGroupScopeFilter;

    abstract public function filter(DiscountLineItem $discount, DiscountPackageCollection $packages, ChannelContext $context): DiscountPackageCollection;
}

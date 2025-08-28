<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\LineItem\Group;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItemFlatCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
interface LineItemGroupRuleMatcherInterface
{
    /**
     * Gets a list of line items that match for the provided group object.
     * You can use AND conditions, OR conditions, or anything else, depending on your implementation.
     */
    public function getMatchingItems(LineItemGroupDefinition $groupDefinition, LineItemFlatCollection $items, ChannelContext $context): LineItemFlatCollection;
}

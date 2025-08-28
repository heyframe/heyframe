<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\LineItem\Group\RulesMatcher;

use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemGroupDefinition;
use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemGroupRuleMatcherInterface;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemFlatCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class AnyRuleMatcher implements LineItemGroupRuleMatcherInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly AbstractAnyRuleLineItemMatcher $anyRuleProvider)
    {
    }

    public function getMatchingItems(
        LineItemGroupDefinition $groupDefinition,
        LineItemFlatCollection $items,
        ChannelContext $context
    ): LineItemFlatCollection {
        $matchingItems = [];

        foreach ($items as $item) {
            if ($this->anyRuleProvider->isMatching($groupDefinition, $item, $context)) {
                $matchingItems[] = $item;
            }
        }

        return new LineItemFlatCollection($matchingItems);
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Checkout\Cart\LineItem\Group\Helpers\Fakes;

use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemGroupDefinition;
use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemGroupRuleMatcherInterface;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemFlatCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @internal
 */
#[Package('checkout')]
class FakeTakeAllRuleMatcher implements LineItemGroupRuleMatcherInterface
{
    private int $sequenceCount = 0;

    public function __construct(private readonly FakeSequenceSupervisor $sequenceSupervisor)
    {
    }

    public function getSequenceCount(): int
    {
        return $this->sequenceCount;
    }

    public function getMatchingItems(LineItemGroupDefinition $groupDefinition, LineItemFlatCollection $items, ChannelContext $context): LineItemFlatCollection
    {
        $this->sequenceCount = $this->sequenceSupervisor->getNextCount();

        return $items;
    }
}

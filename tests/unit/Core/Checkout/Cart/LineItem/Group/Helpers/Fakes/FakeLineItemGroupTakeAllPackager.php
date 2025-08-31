<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Checkout\Cart\LineItem\Group\Helpers\Fakes;

use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemGroup;
use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemGroupPackagerInterface;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemFlatCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @internal
 */
#[Package('checkout')]
class FakeLineItemGroupTakeAllPackager implements LineItemGroupPackagerInterface
{
    private int $sequenceCount = 1;

    public function __construct(
        private readonly string $key,
        private readonly FakeSequenceSupervisor $sequenceSupervisor
    ) {
    }

    public function getSequenceCount(): int
    {
        return $this->sequenceCount;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function buildGroupPackage(float $value, LineItemFlatCollection $sortedItems, ChannelContext $context): LineItemGroup
    {
        $this->sequenceCount = $this->sequenceSupervisor->getNextCount();

        $group = new LineItemGroup();

        foreach ($sortedItems as $item) {
            $group->addItem($item->getId(), $item->getQuantity());
        }

        return $group;
    }
}

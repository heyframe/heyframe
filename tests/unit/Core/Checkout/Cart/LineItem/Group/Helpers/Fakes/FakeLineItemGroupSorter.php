<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Checkout\Cart\LineItem\Group\Helpers\Fakes;

use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemGroupSorterInterface;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemFlatCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('checkout')]
class FakeLineItemGroupSorter implements LineItemGroupSorterInterface
{
    private int $sequenceCount = 0;

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

    public function sort(LineItemFlatCollection $items): LineItemFlatCollection
    {
        $this->sequenceCount = $this->sequenceSupervisor->getNextCount();

        return $items;
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\LineItem\Group;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItemFlatCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
interface LineItemGroupSorterInterface
{
    /**
     * Gets the identifier key for this sorter.
     * Every SetGroup with this sorting key will use this sorter.
     */
    public function getKey(): string;

    /**
     * Gets a sorted list of line items by using
     * the sorting of this implementation.
     */
    public function sort(LineItemFlatCollection $items): LineItemFlatCollection;
}

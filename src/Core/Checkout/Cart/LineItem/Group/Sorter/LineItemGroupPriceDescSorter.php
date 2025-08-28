<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\LineItem\Group\Sorter;

use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemGroupSorterInterface;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemFlatCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class LineItemGroupPriceDescSorter implements LineItemGroupSorterInterface
{
    public function getKey(): string
    {
        return 'PRICE_DESC';
    }

    public function sort(LineItemFlatCollection $items): LineItemFlatCollection
    {
        $sorted = $items->getElements();

        usort($sorted, static function (LineItem $a, LineItem $b) {
            if ($a->getPrice() === null) {
                return 1;
            }

            if ($b->getPrice() === null) {
                return 0;
            }

            return $b->getPrice()->getUnitPrice() <=> $a->getPrice()->getUnitPrice();
        });

        return new LineItemFlatCollection($sorted);
    }
}

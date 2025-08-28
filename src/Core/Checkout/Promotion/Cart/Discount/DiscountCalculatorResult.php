<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Cart\Discount;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\Composition\DiscountCompositionItem;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class DiscountCalculatorResult
{
    /**
     * @param list<DiscountCompositionItem> $compositionItems
     */
    public function __construct(
        private readonly CalculatedPrice $price,
        private readonly array $compositionItems
    ) {
    }

    public function getPrice(): CalculatedPrice
    {
        return $this->price;
    }

    /**
     * @return list<DiscountCompositionItem>
     */
    public function getCompositionItems(): array
    {
        return $this->compositionItems;
    }
}

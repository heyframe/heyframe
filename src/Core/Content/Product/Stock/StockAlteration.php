<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Stock;

use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
final readonly class StockAlteration
{
    public function __construct(
        public string $lineItemId,
        public string $productId,
        public int $quantityBefore,
        public int $newQuantity
    ) {
    }

    public function quantityDelta(): int
    {
        return $this->quantityBefore - $this->newQuantity;
    }
}

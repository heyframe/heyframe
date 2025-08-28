<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Stock;

use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class StockLoadRequest
{
    /**
     * @param array<string> $productIds
     */
    public function __construct(public array $productIds)
    {
    }
}

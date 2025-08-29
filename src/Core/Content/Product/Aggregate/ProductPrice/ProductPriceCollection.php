<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductPrice;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductPriceEntity>
 */
#[Package('inventory')]
class ProductPriceCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'product_price_collection';
    }

    public function filterByRuleId(string $ruleId): self
    {
        return $this->filter(fn (ProductPriceEntity $price) => $ruleId === $price->getRuleId());
    }

    public function sortByQuantity(): void
    {
        $this->sort(fn (ProductPriceEntity $a, ProductPriceEntity $b) => $a->getQuantityStart() <=> $b->getQuantityStart());
    }

    protected function getExpectedClass(): string
    {
        return ProductPriceEntity::class;
    }
}

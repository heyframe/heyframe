<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductCrossSellingAssignedProducts;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductCrossSellingAssignedProductsEntity>
 */
#[Package('inventory')]
class ProductCrossSellingAssignedProductsCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'product_cross_selling_assigned_products_collection';
    }

    /**
     * @return array<string>
     */
    public function getProductIds(): array
    {
        return $this->fmap(fn (ProductCrossSellingAssignedProductsEntity $entity) => $entity->getProductId());
    }

    public function sortByPosition(): void
    {
        $this->sort(fn (ProductCrossSellingAssignedProductsEntity $a, ProductCrossSellingAssignedProductsEntity $b) => $a->getPosition() <=> $b->getPosition());
    }

    protected function getExpectedClass(): string
    {
        return ProductCrossSellingAssignedProductsEntity::class;
    }
}

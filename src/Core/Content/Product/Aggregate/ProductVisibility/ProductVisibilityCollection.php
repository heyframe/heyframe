<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductVisibility;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductVisibilityEntity>
 */
#[Package('inventory')]
class ProductVisibilityCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getProductIds(): array
    {
        return $this->fmap(fn (ProductVisibilityEntity $visibility) => $visibility->getProductId());
    }

    public function filterByProductId(string $id): self
    {
        return $this->filter(fn (ProductVisibilityEntity $visibility) => $visibility->getProductId() === $id);
    }

    public function filterByChannelId(string $id): self
    {
        return $this->filter(fn (ProductVisibilityEntity $visibility) => $visibility->getChannelId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'product_visibility_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductVisibilityEntity::class;
    }
}

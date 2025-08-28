<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductManufacturer;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductManufacturerEntity>
 */
#[Package('inventory')]
class ProductManufacturerCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getMediaIds(): array
    {
        return $this->fmap(fn (ProductManufacturerEntity $productManufacturer) => $productManufacturer->getMediaId());
    }

    public function filterByMediaId(string $id): self
    {
        return $this->filter(fn (ProductManufacturerEntity $productManufacturer) => $productManufacturer->getMediaId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'product_manufacturer_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductManufacturerEntity::class;
    }
}

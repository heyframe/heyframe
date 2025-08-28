<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductMedia;

use HeyFrame\Core\Content\Media\MediaCollection;
use HeyFrame\Core\Content\Media\MediaType\SpatialObjectType;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductMediaEntity>
 */
#[Package('inventory')]
class ProductMediaCollection extends EntityCollection
{
    /**
     * @return array<array-key, string>
     */
    public function getProductIds(): array
    {
        return $this->fmap(fn (ProductMediaEntity $productMedia) => $productMedia->getProductId());
    }

    public function filterByProductId(string $id): self
    {
        return $this->filter(fn (ProductMediaEntity $productMedia) => $productMedia->getProductId() === $id);
    }

    /**
     * @return array<array-key, string>
     */
    public function getMediaIds(): array
    {
        return $this->fmap(fn (ProductMediaEntity $productMedia) => $productMedia->getMediaId());
    }

    public function filterByMediaId(string $id): self
    {
        return $this->filter(fn (ProductMediaEntity $productMedia) => $productMedia->getMediaId() === $id);
    }

    public function getMedia(): MediaCollection
    {
        return new MediaCollection(
            $this->fmap(fn (ProductMediaEntity $productMedia) => $productMedia->getMedia())
        );
    }

    public function getApiAlias(): string
    {
        return 'product_media_collection';
    }

    public function hasSpatialObjects(): bool
    {
        return $this->firstWhere(fn (ProductMediaEntity $productMedia) => $productMedia->getMedia()?->getMediaType() instanceof SpatialObjectType) !== null;
    }

    protected function getExpectedClass(): string
    {
        return ProductMediaEntity::class;
    }
}

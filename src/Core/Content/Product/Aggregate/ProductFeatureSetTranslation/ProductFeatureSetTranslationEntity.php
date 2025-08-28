<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductFeatureSetTranslation;

use HeyFrame\Core\Content\Product\Aggregate\ProductFeatureSet\ProductFeatureSetEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\TranslationEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductFeatureSetTranslationEntity extends TranslationEntity
{
    protected string $productFeatureSetId;

    protected ?string $name = null;

    protected ?string $description = null;

    protected ProductFeatureSetEntity $productFeatureSet;

    public function getProductFeatureSetId(): string
    {
        return $this->productFeatureSetId;
    }

    public function setProductFeatureSetId(string $productFeatureSetId): void
    {
        $this->productFeatureSetId = $productFeatureSetId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getProductFeatureSet(): ProductFeatureSetEntity
    {
        return $this->productFeatureSet;
    }

    public function setProductFeatureSet(ProductFeatureSetEntity $productFeatureSet): void
    {
        $this->productFeatureSet = $productFeatureSet;
    }
}

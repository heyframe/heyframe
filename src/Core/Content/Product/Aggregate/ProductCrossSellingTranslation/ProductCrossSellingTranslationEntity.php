<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductCrossSellingTranslation;

use HeyFrame\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\TranslationEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductCrossSellingTranslationEntity extends TranslationEntity
{
    protected string $productCrossSellingId;

    protected ?string $name = null;

    protected ?ProductCrossSellingEntity $productCrossSelling = null;

    public function getProductCrossSellingId(): string
    {
        return $this->productCrossSellingId;
    }

    public function setProductCrossSellingId(string $productCrossSellingId): void
    {
        $this->productCrossSellingId = $productCrossSellingId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getProductCrossSelling(): ?ProductCrossSellingEntity
    {
        return $this->productCrossSelling;
    }

    public function setProductCrossSelling(ProductCrossSellingEntity $productCrossSelling): void
    {
        $this->productCrossSelling = $productCrossSelling;
    }
}

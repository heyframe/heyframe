<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductFeatureSet;

use HeyFrame\Core\Content\Product\Aggregate\ProductFeatureSetTranslation\ProductFeatureSetTranslationCollection;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductFeatureSetEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $name = null;

    protected ?string $description = null;

    /**
     * @var array<int, array{name: string, id: string, type: string, position: int}>|null
     */
    protected ?array $features = null;

    protected ?ProductFeatureSetTranslationCollection $translations = null;

    protected ?ProductCollection $products = null;

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

    /**
     * @return array<int, array{name: string, id: string, type: string, position: int}>|null
     */
    public function getFeatures(): ?array
    {
        return $this->features;
    }

    /**
     * @param array<int, array{name: string, id: string, type: string, position: int}> $features
     */
    public function setFeatures(array $features): void
    {
        $this->features = $features;
    }

    public function getTranslations(): ?ProductFeatureSetTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(ProductFeatureSetTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getProducts(): ?ProductCollection
    {
        return $this->products;
    }

    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }
}

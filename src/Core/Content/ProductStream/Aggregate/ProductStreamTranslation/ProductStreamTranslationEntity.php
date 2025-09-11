<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ProductStream\Aggregate\ProductStreamTranslation;

use HeyFrame\Core\Content\ProductStream\ProductStreamEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\TranslationEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductStreamTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected string $productStreamId;

    protected ?string $name = null;

    protected ?string $description = null;

    protected ?ProductStreamEntity $productStream = null;

    public function getProductStreamId(): string
    {
        return $this->productStreamId;
    }

    public function setProductStreamId(string $productStreamId): void
    {
        $this->productStreamId = $productStreamId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
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

    public function getProductStream(): ?ProductStreamEntity
    {
        return $this->productStream;
    }

    public function setProductStream(?ProductStreamEntity $productStream): void
    {
        $this->productStream = $productStream;
    }
}

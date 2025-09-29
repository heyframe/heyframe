<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\MainCategory;

use HeyFrame\Core\Content\Category\CategoryEntity;
use HeyFrame\Core\Content\Product\ProductEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelEntity;

#[Package('inventory')]
class MainCategoryEntity extends Entity
{
    use EntityIdTrait;

    protected string $channelId;

    protected ?ChannelEntity $channel = null;

    protected string $categoryId;

    protected string $categoryVersionId;

    protected ?CategoryEntity $category = null;

    protected string $productId;

    protected string $productVersionId;

    protected ?ProductEntity $product = null;

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function setChannelId(string $channelId): void
    {
        $this->channelId = $channelId;
    }

    public function getChannel(): ?ChannelEntity
    {
        return $this->channel;
    }

    public function setChannel(?ChannelEntity $channel): void
    {
        $this->channel = $channel;
    }

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    public function setCategoryId(string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @deprecated tag:v6.8.0 - reason:return-type-change - return type will be nullable and condition will be removed
     */
    public function getCategory(): CategoryEntity
    {
        if ($this->category === null) {
            return new CategoryEntity();
        }

        return $this->category;
    }

    public function setCategory(CategoryEntity $category): void
    {
        $this->category = $category;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getCategoryVersionId(): string
    {
        return $this->categoryVersionId;
    }

    public function setCategoryVersionId(string $categoryVersionId): void
    {
        $this->categoryVersionId = $categoryVersionId;
    }

    public function getProductVersionId(): string
    {
        return $this->productVersionId;
    }

    public function setProductVersionId(string $productVersionId): void
    {
        $this->productVersionId = $productVersionId;
    }
}

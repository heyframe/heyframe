<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product;

use HeyFrame\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use HeyFrame\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingCollection;
use HeyFrame\Core\Content\Product\Aggregate\ProductMedia\ProductMediaCollection;
use HeyFrame\Core\Content\Product\Aggregate\ProductMedia\ProductMediaEntity;
use HeyFrame\Core\Content\Product\Aggregate\ProductPrice\ProductPriceCollection;
use HeyFrame\Core\Content\Product\Aggregate\ProductTranslation\ProductTranslationCollection;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityCollection;
use HeyFrame\Core\Content\Product\DataAbstractionLayer\VariantListingConfig;
use HeyFrame\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\Price;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetCollection;
use HeyFrame\Core\System\Tag\TagCollection;

#[Package('inventory')]
class ProductEntity extends Entity implements \Stringable
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    final public const PRODUCT_TYPE_MEMBERSHIP_PLAN = 'membership_plan';
    final public const PRODUCT_TYPE_WALLET_RECHARGE = 'wallet_recharge';

    protected ?string $parentId = null;

    protected int $childCount = 0;

    protected int $autoIncrement;

    protected ?bool $active = null;

    protected ?string $displayGroup = null;

    protected ?PriceCollection $price = null;

    protected int $sales;

    protected string $productNumber;

    protected int $stock;

    protected ?int $availableStock = null;

    protected bool $available;

    protected ?int $restockTime = null;

    protected ?bool $isCloseout = null;

    protected ?int $purchaseSteps = null;

    protected ?int $maxPurchase = null;

    protected ?int $minPurchase = null;

    protected ?PriceCollection $purchasePrices = null;

    protected ?\DateTimeInterface $releaseDate = null;

    /**
     * @var array<string>|null
     */
    protected ?array $optionIds = null;

    /**
     * @var array<string>|null
     */
    protected ?array $propertyIds = null;

    protected ?string $name = null;

    protected ?string $keywords = null;

    protected ?string $description = null;

    protected string $productType;

    protected ?string $metaDescription = null;

    protected ?string $metaTitle = null;

    /**
     * @var array<string>|null
     */
    protected ?array $variantRestrictions = null;

    protected ?VariantListingConfig $variantListingConfig = null;

    /**
     * @var array<array<string>>
     */
    protected array $variation = [];

    protected ProductPriceCollection $prices;

    protected ?ProductMediaEntity $cover = null;

    protected ?ProductEntity $parent = null;

    protected ?ProductCollection $children = null;

    protected ?ProductMediaCollection $media = null;

    protected ?string $cmsPageId = null;

    /**
     * @var array<string, array<string, array<string, string>>>|null
     */
    protected ?array $slotConfig = null;

    protected ?ProductTranslationCollection $translations = null;

    protected ?CustomFieldSetCollection $customFieldSets = null;

    protected ?TagCollection $tags = null;

    protected ?PropertyGroupOptionCollection $properties = null;

    protected ?PropertyGroupOptionCollection $options = null;

    protected ?ProductConfiguratorSettingCollection $configuratorSettings = null;

    protected ?string $coverId = null;

    protected ?ProductVisibilityCollection $visibilities = null;

    /**
     * @var array<string>|null
     */
    protected ?array $tagIds = null;

    protected ?OrderLineItemCollection $orderLineItems = null;

    protected ?bool $customFieldSetSelectionActive = null;

    protected ?string $canonicalProductId = null;

    protected ?ProductEntity $canonicalProduct = null;

    public function __construct()
    {
        $this->prices = new ProductPriceCollection();
    }

    public function __toString(): string
    {
        return (string) ($this->getTranslation('name') ?? $this->getName());
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }

    public function getPrice(): ?PriceCollection
    {
        return $this->price;
    }

    public function setPrice(PriceCollection $price): void
    {
        $this->price = $price;
    }

    public function getCurrencyPrice(string $currencyId): ?Price
    {
        return $this->price?->getCurrencyPrice($currencyId);
    }

    public function getSales(): int
    {
        return $this->sales;
    }

    public function setSales(int $sales): void
    {
        $this->sales = $sales;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    public function getIsCloseout(): bool
    {
        return (bool) $this->isCloseout;
    }

    public function setIsCloseout(?bool $isCloseout): void
    {
        $this->isCloseout = $isCloseout;
    }

    public function getPurchaseSteps(): ?int
    {
        return $this->purchaseSteps;
    }

    public function setPurchaseSteps(?int $purchaseSteps): void
    {
        $this->purchaseSteps = $purchaseSteps;
    }

    public function getMaxPurchase(): ?int
    {
        return $this->maxPurchase;
    }

    public function setMaxPurchase(?int $maxPurchase): void
    {
        $this->maxPurchase = $maxPurchase;
    }

    public function getMinPurchase(): ?int
    {
        return $this->minPurchase;
    }

    public function setMinPurchase(?int $minPurchase): void
    {
        $this->minPurchase = $minPurchase;
    }

    public function getPurchasePrices(): ?PriceCollection
    {
        return $this->purchasePrices;
    }

    public function setPurchasePrices(?PriceCollection $purchasePrices): void
    {
        $this->purchasePrices = $purchasePrices;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?\DateTimeInterface $releaseDate): void
    {
        $this->releaseDate = $releaseDate;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(?string $keywords): void
    {
        $this->keywords = $keywords;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getProductType(): string
    {
        return $this->productType;
    }

    public function setProductType(string $productType): void
    {
        $this->productType = $productType;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    public function getPrices(): ?ProductPriceCollection
    {
        return $this->prices;
    }

    public function setPrices(ProductPriceCollection $prices): void
    {
        $this->prices = $prices;
    }

    public function getRestockTime(): ?int
    {
        return $this->restockTime;
    }

    public function setRestockTime(?int $restockTime): void
    {
        $this->restockTime = $restockTime;
    }

    public function isReleased(): bool
    {
        if (!$this->getReleaseDate()) {
            return true;
        }

        return $this->releaseDate < new \DateTime();
    }

    /**
     * @return array<string>|null
     */
    public function getOptionIds(): ?array
    {
        return $this->optionIds;
    }

    /**
     * @param array<string>|null $optionIds
     */
    public function setOptionIds(?array $optionIds): void
    {
        $this->optionIds = $optionIds;
    }

    /**
     * @return array<string>|null
     */
    public function getPropertyIds(): ?array
    {
        return $this->propertyIds;
    }

    /**
     * @param array<string>|null $propertyIds
     */
    public function setPropertyIds(?array $propertyIds): void
    {
        $this->propertyIds = $propertyIds;
    }

    public function getCover(): ?ProductMediaEntity
    {
        return $this->cover;
    }

    public function setCover(ProductMediaEntity $cover): void
    {
        $this->cover = $cover;
    }

    public function getCmsPageId(): ?string
    {
        return $this->cmsPageId;
    }

    public function setCmsPageId(string $cmsPageId): void
    {
        $this->cmsPageId = $cmsPageId;
    }

    /**
     * @return array<string, array<string, array<string, string>>>|null
     */
    public function getSlotConfig(): ?array
    {
        return $this->slotConfig;
    }

    /**
     * @param array<string, array<string, array<string, string>>> $slotConfig
     */
    public function setSlotConfig(array $slotConfig): void
    {
        $this->slotConfig = $slotConfig;
    }

    public function getParent(): ?ProductEntity
    {
        return $this->parent;
    }

    public function setParent(ProductEntity $parent): void
    {
        $this->parent = $parent;
    }

    public function getChildren(): ?ProductCollection
    {
        return $this->children;
    }

    public function setChildren(ProductCollection $children): void
    {
        $this->children = $children;
    }

    public function getMedia(): ?ProductMediaCollection
    {
        return $this->media;
    }

    public function setMedia(ProductMediaCollection $media): void
    {
        $this->media = $media;
    }

    public function getTranslations(): ?ProductTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(ProductTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getCustomFieldSets(): ?CustomFieldSetCollection
    {
        return $this->customFieldSets;
    }

    public function setCustomFieldSets(CustomFieldSetCollection $customFieldSets): void
    {
        $this->customFieldSets = $customFieldSets;
    }

    public function getTags(): ?TagCollection
    {
        return $this->tags;
    }

    public function setTags(TagCollection $tags): void
    {
        $this->tags = $tags;
    }

    public function getProperties(): ?PropertyGroupOptionCollection
    {
        return $this->properties;
    }

    public function setProperties(PropertyGroupOptionCollection $properties): void
    {
        $this->properties = $properties;
    }

    public function getOptions(): ?PropertyGroupOptionCollection
    {
        return $this->options;
    }

    public function setOptions(PropertyGroupOptionCollection $options): void
    {
        $this->options = $options;
    }

    public function getConfiguratorSettings(): ?ProductConfiguratorSettingCollection
    {
        return $this->configuratorSettings;
    }

    public function setConfiguratorSettings(ProductConfiguratorSettingCollection $configuratorSettings): void
    {
        $this->configuratorSettings = $configuratorSettings;
    }

    public function getAutoIncrement(): int
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement(int $autoIncrement): void
    {
        $this->autoIncrement = $autoIncrement;
    }

    public function getCoverId(): ?string
    {
        return $this->coverId;
    }

    public function setCoverId(string $coverId): void
    {
        $this->coverId = $coverId;
    }

    public function getVisibilities(): ?ProductVisibilityCollection
    {
        return $this->visibilities;
    }

    public function setVisibilities(ProductVisibilityCollection $visibilities): void
    {
        $this->visibilities = $visibilities;
    }

    public function getProductNumber(): string
    {
        return $this->productNumber;
    }

    public function setProductNumber(string $productNumber): void
    {
        $this->productNumber = $productNumber;
    }

    /**
     * @return array<string>|null
     */
    public function getTagIds(): ?array
    {
        return $this->tagIds;
    }

    /**
     * @param array<string> $tagIds
     */
    public function setTagIds(array $tagIds): void
    {
        $this->tagIds = $tagIds;
    }

    /**
     * @return array<string>|null
     */
    public function getVariantRestrictions(): ?array
    {
        return $this->variantRestrictions;
    }

    /**
     * @param array<string>|null $variantRestrictions
     */
    public function setVariantRestrictions(?array $variantRestrictions): void
    {
        $this->variantRestrictions = $variantRestrictions;
    }

    public function getVariantListingConfig(): ?VariantListingConfig
    {
        return $this->variantListingConfig;
    }

    public function setVariantListingConfig(?VariantListingConfig $variantListingConfig): void
    {
        $this->variantListingConfig = $variantListingConfig;
    }

    /**
     * @return array<array<string>>
     */
    public function getVariation(): array
    {
        return $this->variation;
    }

    /**
     * @param array<array<string>> $variation
     */
    public function setVariation(array $variation): void
    {
        $this->variation = $variation;
    }

    public function getAvailableStock(): ?int
    {
        return $this->availableStock;
    }

    public function setAvailableStock(int $availableStock): void
    {
        $this->availableStock = $availableStock;
    }

    public function getAvailable(): bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): void
    {
        $this->available = $available;
    }

    public function getChildCount(): ?int
    {
        return $this->childCount;
    }

    public function setChildCount(int $childCount): void
    {
        $this->childCount = $childCount;
    }

    public function getDisplayGroup(): ?string
    {
        return $this->displayGroup;
    }

    public function setDisplayGroup(?string $displayGroup): void
    {
        $this->displayGroup = $displayGroup;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getOrderLineItems(): ?OrderLineItemCollection
    {
        return $this->orderLineItems;
    }

    public function setOrderLineItems(OrderLineItemCollection $orderLineItems): void
    {
        $this->orderLineItems = $orderLineItems;
    }

    public function getCustomFieldSetSelectionActive(): ?bool
    {
        return $this->customFieldSetSelectionActive;
    }

    public function setCustomFieldSetSelectionActive(?bool $customFieldSetSelectionActive): void
    {
        $this->customFieldSetSelectionActive = $customFieldSetSelectionActive;
    }

    public function getCanonicalProductId(): ?string
    {
        return $this->canonicalProductId;
    }

    public function setCanonicalProductId(string $canonicalProductId): void
    {
        $this->canonicalProductId = $canonicalProductId;
    }

    public function getCanonicalProduct(): ?ProductEntity
    {
        return $this->canonicalProduct;
    }

    public function setCanonicalProduct(ProductEntity $product): void
    {
        $this->canonicalProduct = $product;
    }
}

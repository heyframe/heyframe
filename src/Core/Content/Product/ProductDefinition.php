<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product;

use HeyFrame\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use HeyFrame\Core\Content\Category\CategoryDefinition;
use HeyFrame\Core\Content\Cms\CmsPageDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductCategory\ProductCategoryDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductCategoryTree\ProductCategoryTreeDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductCustomFieldSet\ProductCustomFieldSetDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductMedia\ProductMediaDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductOption\ProductOptionDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductPrice\ProductPriceDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductProperty\ProductPropertyDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductReview\ProductReviewDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductTag\ProductTagDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductTranslation\ProductTranslationDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use HeyFrame\Core\Content\Seo\MainCategory\MainCategoryDefinition;
use HeyFrame\Core\Content\Seo\SeoUrl\SeoUrlDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\AutoIncrementField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ChildCountField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ChildrenAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ExtraFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiCriteriaAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\NoConstraint;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FloatField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ListField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyIdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ParentAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ParentFkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\PriceField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\VariantListingConfigField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\VersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetDefinition;
use HeyFrame\Core\System\NumberRange\DataAbstractionLayer\NumberRangeField;
use HeyFrame\Core\System\Tag\TagDefinition;

#[Package('inventory')]
class ProductDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'product';

    final public const CONFIG_KEY_DEFAULT_CMS_PAGE_PRODUCT = 'core.cms.default_product_cms_page';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function isInheritanceAware(): bool
    {
        return true;
    }

    public function getCollectionClass(): string
    {
        return ProductCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProductEntity::class;
    }

    /**
     * @return array<string, bool|int|null>
     */
    public function getDefaults(): array
    {
        return [
            'minPurchase' => 1,
            'isCloseout' => false,
            'purchaseSteps' => 1,
            'restockTime' => null,
            'active' => true,
        ];
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    public function getHydratorClass(): string
    {
        return ProductHydrator::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new VersionField())->addFlags(new ApiAware()),
            (new ParentFkField(self::class))->addFlags(new ApiAware()),
            (new ReferenceVersionField(self::class, 'parent_version_id'))->addFlags(new ApiAware(), new Required()),

            (new FkField('product_media_id', 'coverId', ProductMediaDefinition::class))->addFlags(new ApiAware(), new Inherited(), new NoConstraint()),
            (new ReferenceVersionField(ProductMediaDefinition::class))->addFlags(new ApiAware(), new Inherited(), new Required()),
            (new FkField('canonical_product_id', 'canonicalProductId', self::class))->addFlags(new ApiAware(), new Inherited()),
            (new ReferenceVersionField(self::class, 'canonical_product_version_id'))->addFlags(new ApiAware(), new Inherited(), new Required()),

            (new PriceField('price', 'price'))->addFlags(new Inherited(), new Required(), new ApiCriteriaAware()),
            (new NumberRangeField('product_number', 'productNumber'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING, false), new Required()),
            (new IntField('restock_time', 'restockTime'))->addFlags(new ApiAware(), new Inherited()),
            new AutoIncrementField(),
            (new BoolField('active', 'active'))->addFlags(new ApiAware(), new Inherited()),
            (new BoolField('available', 'available'))->addFlags(new ApiAware(), new WriteProtected()),
            (new BoolField('is_closeout', 'isCloseout'))->addFlags(new ApiAware(), new Inherited()),
            (new IntField('available_stock', 'availableStock'))->addFlags(new ApiAware(), new WriteProtected()),
            (new IntField('stock', 'stock'))->addFlags(new ApiAware(), new Required()),

            (new ListField('variation', 'variation', StringField::class))->addFlags(new Runtime(['options.name', 'options.group.name'])),
            (new StringField('display_group', 'displayGroup'))->addFlags(new ApiAware(), new WriteProtected()),
            (new VariantListingConfigField('variant_listing_config', 'variantListingConfig'))->addFlags(new Inherited()),
            new JsonField('variant_restrictions', 'variantRestrictions'),
            (new IntField('purchase_steps', 'purchaseSteps', 1))->addFlags(new ApiAware(), new Inherited()),
            (new FkField('cms_page_id', 'cmsPageId', CmsPageDefinition::class))->addFlags(new ApiAware(), new Inherited()),

            (new IntField('max_purchase', 'maxPurchase'))->addFlags(new ApiAware(), new Inherited()),
            (new IntField('min_purchase', 'minPurchase', 1))->addFlags(new ApiAware(), new Inherited()),
            (new FloatField('purchase_unit', 'purchaseUnit'))->addFlags(new ApiAware(), new Inherited()),
            (new PriceField('purchase_prices', 'purchasePrices'))->addFlags(new Inherited()),
            (new DateTimeField('release_date', 'releaseDate'))->addFlags(new ApiAware(), new Inherited()),
            (new FloatField('rating_average', 'ratingAverage'))->addFlags(new ApiAware(), new WriteProtected(), new Inherited()),
            (new ManyToManyIdField('property_ids', 'propertyIds', 'properties'))->addFlags(new ApiAware(), new Inherited()),
            (new ManyToManyIdField('option_ids', 'optionIds', 'options'))->addFlags(new ApiAware(), new Inherited()),
            (new ManyToManyIdField('tag_ids', 'tagIds', 'tags'))->addFlags(new Inherited(), new ApiAware()),
            (new ManyToManyIdField('category_ids', 'categoryIds', 'categories'))->addFlags(new ApiAware(), new Inherited()),
            (new ChildCountField())->addFlags(new ApiAware()),
            (new IntField('sales', 'sales'))->addFlags(new ApiAware(), new WriteProtected()),
            (new ListField('states', 'states', StringField::class))->addFlags(new ApiAware(), new WriteProtected()),
            (new StringField('product_type', 'productType'))->addFlags(new ApiAware(), new Inherited()),
            (new ExtraFields())->addFlags(new ApiAware()),
            (new TranslatedField('metaDescription'))->addFlags(new ApiAware(), new Inherited()),
            (new TranslatedField('name', true))->addFlags(new ApiAware(), new Inherited(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('keywords'))->addFlags(new ApiAware(), new Inherited()),
            (new TranslatedField('description'))->addFlags(new ApiAware(), new Inherited()),
            (new TranslatedField('metaTitle'))->addFlags(new ApiAware(), new Inherited()),
            (new TranslatedField('customFields'))->addFlags(new ApiAware(), new Inherited()),

            // associations
            (new ParentAssociationField(self::class, 'id'))->addFlags(new ApiAware()),
            (new ChildrenAssociationField(self::class))->addFlags(new ApiAware()),

            (new ManyToOneAssociationField('cover', 'product_media_id', ProductMediaDefinition::class, 'id'))->addFlags(new ApiAware(), new Inherited()),

            (new ManyToOneAssociationField('canonicalProduct', 'canonical_product_id', ProductDefinition::class, 'id'))->addFlags(new ApiAware(), new Inherited()),

            (new OneToManyAssociationField('prices', ProductPriceDefinition::class, 'product_id'))->addFlags(new CascadeDelete(), new Inherited()),

            (new OneToManyAssociationField('media', ProductMediaDefinition::class, 'product_id'))->addFlags(new ApiAware(), new CascadeDelete(), new Inherited()),
            (new OneToManyAssociationField('seoUrls', SeoUrlDefinition::class, 'foreign_key'))->addFlags(new ApiAware()),

            (new OneToManyAssociationField('configuratorSettings', ProductConfiguratorSettingDefinition::class, 'product_id', 'id'))->addFlags(new ApiAware(), new CascadeDelete()),

            (new OneToManyAssociationField('visibilities', ProductVisibilityDefinition::class, 'product_id'))->addFlags(new CascadeDelete(), new Inherited()),
            (new OneToManyAssociationField('mainCategories', MainCategoryDefinition::class, 'product_id'))->addFlags(new ApiAware(), new CascadeDelete()),

            (new OneToManyAssociationField('productReviews', ProductReviewDefinition::class, 'product_id'))->addFlags(new ApiAware(), new CascadeDelete(false)),
            (new OneToManyAssociationField('orderLineItems', OrderLineItemDefinition::class, 'product_id'))->addFlags(new SetNullOnDelete()),

            (new ManyToManyAssociationField('options', PropertyGroupOptionDefinition::class, ProductOptionDefinition::class, 'product_id', 'property_group_option_id'))->addFlags(new ApiAware(), new CascadeDelete()),
            (new ManyToManyAssociationField('tags', TagDefinition::class, ProductTagDefinition::class, 'product_id', 'tag_id'))->addFlags(new CascadeDelete(), new Inherited(), new ApiAware()),
            (new ManyToManyAssociationField('categories', CategoryDefinition::class, ProductCategoryDefinition::class, 'product_id', 'category_id'))->addFlags(new ApiAware(), new CascadeDelete(), new Inherited()),
            (new ManyToManyAssociationField('categoriesRo', CategoryDefinition::class, ProductCategoryTreeDefinition::class, 'product_id', 'category_id'))->addFlags(new ApiAware(), new CascadeDelete(false), new WriteProtected()),
            (new ManyToManyAssociationField('customFieldSets', CustomFieldSetDefinition::class, ProductCustomFieldSetDefinition::class, 'product_id', 'custom_field_set_id'))->addFlags(new CascadeDelete(), new Inherited()),
            (new ManyToOneAssociationField('cmsPage', 'cms_page_id', CmsPageDefinition::class, 'id', false))->addFlags(new ApiAware(), new Inherited()),

            (new ManyToManyAssociationField('properties', PropertyGroupOptionDefinition::class, ProductPropertyDefinition::class, 'product_id', 'property_group_option_id'))->addFlags(new ApiAware(), new CascadeDelete(), new Inherited()),
            (new TranslationsAssociationField(ProductTranslationDefinition::class, 'product_id'))->addFlags(new ApiAware(), new Inherited(), new Required()),
        ]);
    }
}

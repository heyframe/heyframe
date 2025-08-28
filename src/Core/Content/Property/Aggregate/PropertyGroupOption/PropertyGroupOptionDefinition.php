<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Property\Aggregate\PropertyGroupOption;

use HeyFrame\Core\Content\Media\MediaDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductOption\ProductOptionDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductProperty\ProductPropertyDefinition;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Content\Property\Aggregate\PropertyGroupOptionTranslation\PropertyGroupOptionTranslationDefinition;
use HeyFrame\Core\Content\Property\PropertyGroupDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ReverseInherited;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class PropertyGroupOptionDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'property_group_option';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PropertyGroupOptionCollection::class;
    }

    public function getEntityClass(): string
    {
        return PropertyGroupOptionEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    public function getHydratorClass(): string
    {
        return PropertyGroupOptionHydrator::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return PropertyGroupDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('property_group_id', 'groupId', PropertyGroupDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new TranslatedField('name'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('position'))->addFlags(new ApiAware()),
            (new StringField('color_hex_code', 'colorHexCode'))->addFlags(new ApiAware()),
            (new FkField('media_id', 'mediaId', MediaDefinition::class))->addFlags(new ApiAware()),
            (new BoolField('combinable', 'combinable'))->addFlags(new ApiAware(), new Runtime()),
            (new TranslatedField('customFields'))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id'))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('group', 'property_group_id', PropertyGroupDefinition::class, 'id'))->addFlags(new ApiAware()),
            (new TranslationsAssociationField(PropertyGroupOptionTranslationDefinition::class, 'property_group_option_id'))->addFlags(new Required()),
            (new OneToManyAssociationField('productConfiguratorSettings', ProductConfiguratorSettingDefinition::class, 'property_group_option_id', 'id'))->addFlags(new RestrictDelete()),
            (new ManyToManyAssociationField('productProperties', ProductDefinition::class, ProductPropertyDefinition::class, 'property_group_option_id', 'product_id'))->addFlags(new CascadeDelete(), new ReverseInherited('properties')),
            (new ManyToManyAssociationField('productOptions', ProductDefinition::class, ProductOptionDefinition::class, 'property_group_option_id', 'product_id'))->addFlags(new RestrictDelete()),
        ]);
    }
}

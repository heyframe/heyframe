<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductCrossSellingTranslation;

use HeyFrame\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductCrossSellingTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'product_cross_selling_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ProductCrossSellingTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProductCrossSellingTranslationEntity::class;
    }

    public function since(): ?string
    {
        return '6.1.0.0';
    }

    protected function getParentDefinitionClass(): string
    {
        return ProductCrossSellingDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new ApiAware(), new Required()),
        ]);
    }
}

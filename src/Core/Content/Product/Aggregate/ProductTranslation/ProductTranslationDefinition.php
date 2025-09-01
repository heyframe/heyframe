<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductTranslation;

use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'product_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function isVersionAware(): bool
    {
        return true;
    }

    public function getCollectionClass(): string
    {
        return ProductTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProductTranslationEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): string
    {
        return ProductDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('meta_description', 'metaDescription'))->addFlags(new ApiAware()),
            (new StringField('name', 'name'))->addFlags(new ApiAware(), new Required()),
            (new LongTextField('keywords', 'keywords'))->addFlags(new ApiAware()),
            (new LongTextField('description', 'description'))->addFlags(new ApiAware(), new AllowHtml()),
            (new StringField('meta_title', 'metaTitle'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware()),
        ]);
    }
}

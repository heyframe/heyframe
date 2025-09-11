<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ProductStream\Aggregate\ProductStreamTranslation;

use HeyFrame\Core\Content\ProductStream\ProductStreamDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductStreamTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'product_stream_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ProductStreamTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProductStreamTranslationEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): string
    {
        return ProductStreamDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new ApiAware(), new Required()),
            (new LongTextField('description', 'description'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware()),
        ]);
    }
}

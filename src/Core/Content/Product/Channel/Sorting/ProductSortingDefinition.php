<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Sorting;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\LockedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductSortingDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'product_sorting';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ProductSortingEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProductSortingCollection::class;
    }

    public function since(): ?string
    {
        return '6.3.2.0';
    }

    public function getHydratorClass(): string
    {
        return ProductSortingHydrator::class;
    }

    protected function defineFields(): FieldCollection
    {
        $collection = new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new LockedField(),
            (new StringField('url_key', 'key'))->addFlags(new ApiAware(), new Required()),
            (new IntField('priority', 'priority'))->addFlags(new ApiAware(), new Required()),
            (new BoolField('active', 'active'))->addFlags(new Required()),
            (new JsonField('fields', 'fields'))->addFlags(new Required()),
            (new TranslatedField('label'))->addFlags(new ApiAware()),
            (new TranslationsAssociationField(ProductSortingTranslationDefinition::class, 'product_sorting_id'))->addFlags(new Inherited(), new Required()),
        ]);

        return $collection;
    }
}

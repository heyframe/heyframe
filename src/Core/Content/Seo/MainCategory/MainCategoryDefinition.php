<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\MainCategory;

use HeyFrame\Core\Content\Category\CategoryDefinition;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelDefinition;

#[Package('inventory')]
class MainCategoryDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'main_category';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return MainCategoryCollection::class;
    }

    public function getEntityClass(): string
    {
        return MainCategoryEntity::class;
    }

    public function isInheritanceAware(): bool
    {
        return false;
    }

    public function isVersionAware(): bool
    {
        return false;
    }

    public function since(): ?string
    {
        return '6.1.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),

            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new FkField('category_id', 'categoryId', CategoryDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(CategoryDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FkField('channel_id', 'channelId', ChannelDefinition::class))->addFlags(new ApiAware(), new Required()),
            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class),
            new ManyToOneAssociationField('category', 'category_id', CategoryDefinition::class),
            new ManyToOneAssociationField('channel', 'channel_id', ChannelDefinition::class),
        ]);
    }
}

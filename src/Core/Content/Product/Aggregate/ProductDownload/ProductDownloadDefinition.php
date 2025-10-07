<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductDownload;

use HeyFrame\Core\Content\Media\MediaDefinition;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\VersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductDownloadDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'product_download';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ProductDownloadCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProductDownloadEntity::class;
    }

    public function since(): ?string
    {
        return '6.4.19.0';
    }

    protected function getParentDefinitionClass(): ?string
    {
        return ProductDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new VersionField())->addFlags(new ApiAware()),

            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new FkField('media_id', 'mediaId', MediaDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new IntField('position', 'position'))->addFlags(new ApiAware()),

            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id'))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware()),
        ]);
    }
}

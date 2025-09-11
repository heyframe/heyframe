<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductCrossSellingAssignedProducts;

use HeyFrame\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingDefinition;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductCrossSellingAssignedProductsDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'product_cross_selling_assigned_products';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ProductCrossSellingAssignedProductsEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProductCrossSellingAssignedProductsCollection::class;
    }

    public function since(): ?string
    {
        return '6.2.0.0';
    }

    public function getHydratorClass(): string
    {
        return ProductCrossSellingAssignedProductsHydrator::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return ProductCrossSellingDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('cross_selling_id', 'crossSellingId', ProductCrossSellingDefinition::class))->addFlags(new Required()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class),
            new ManyToOneAssociationField('crossSelling', 'cross_selling_id', ProductCrossSellingDefinition::class),
            new IntField('position', 'position'),
        ]);
    }
}

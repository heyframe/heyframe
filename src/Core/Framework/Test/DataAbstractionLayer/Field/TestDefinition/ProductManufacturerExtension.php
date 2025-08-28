<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\DataAbstractionLayer\Field\TestDefinition;

use HeyFrame\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtension;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('inventory')]
class ProductManufacturerExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToOneAssociationField('toOne', 'id', 'product_id', ExtendedProductManufacturerDefinition::class, false)
        );
        $collection->add(
            new OneToManyAssociationField('oneToMany', ExtendedProductManufacturerDefinition::class, 'product_id', 'id')
        );
    }

    public function getEntityName(): string
    {
        return ProductManufacturerDefinition::ENTITY_NAME;
    }
}

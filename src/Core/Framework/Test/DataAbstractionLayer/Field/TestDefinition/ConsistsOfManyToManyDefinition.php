<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\DataAbstractionLayer\Field\TestDefinition;

use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

/**
 * @internal
 */
class ConsistsOfManyToManyDefinition extends MappingEntityDefinition
{
    public function getEntityName(): string
    {
        return 'acme_consists_of_mapping';
    }

    public function since(): string
    {
        return '6.6.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('product_id_to', 'productIdTo', ProductDefinition::class))->addFlags(new PrimaryKey(), new Required()),

            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),

            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id', true))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('productTo', 'product_id_to', ProductDefinition::class, 'id', true))->addFlags(new ApiAware()),
        ]);
    }
}

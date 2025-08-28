<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\DataAbstractionLayer\Field\TestDefinition;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * @internal
 */
class SingleEntityDependencyTestRootDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = '_test_pickup_point';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('name', 'name'))->addFlags(new Required()),
            (new FkField('warehouse_id', 'warehouseId', SingleEntityDependencyTestSubDefinition::class, 'id'))->addFlags(new Required()),
            (new FkField('zipcode_id', 'zipcodeId', SingleEntityDependencyTestDependencyDefinition::class, 'id'))->addFlags(new Required()),

            new ManyToOneAssociationField('warehouse', 'warehouse_id', SingleEntityDependencyTestSubDefinition::class, 'id'),
            new ManyToOneAssociationField('zipcode', 'zipcode_id', SingleEntityDependencyTestDependencyDefinition::class, 'id'),
        ]);
    }
}

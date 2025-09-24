<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Acl\Front\Role;

use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class CustomerRoleMappingDefinition extends MappingEntityDefinition
{
    final public const ENTITY_NAME = 'customer_role_mapping';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('customer_id', 'customerId', CustomerDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('customer_role_id', 'aclRoleId', CustomerRoleDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new CreatedAtField(),
            new UpdatedAtField(),
            new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class),
            new ManyToOneAssociationField('customerRole', 'customer_role_id', CustomerRoleDefinition::class),
        ]);
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Acl\Front\Role;

use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ExtraFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ListField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

#[Package('framework')]
class CustomerRoleDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'customer_role';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return CustomerRoleCollection::class;
    }

    public function getEntityClass(): string
    {
        return CustomerRoleEntity::class;
    }

    public function getDefaults(): array
    {
        return ['privileges' => []];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('name', 'name'))->addFlags(new Required()),
            new JsonField('config', 'config', [], []),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new ExtraFields())->addFlags(new ApiAware()),
            (new ListField('privileges', 'privileges', StringField::class))->addFlags(new Required()),
            new DateTimeField('deleted_at', 'deletedAt'),
            new ManyToManyAssociationField('customers', CustomerDefinition::class, CustomerRoleMappingDefinition::class, 'customer_role_id', 'customer_id'),
        ]);
    }
}

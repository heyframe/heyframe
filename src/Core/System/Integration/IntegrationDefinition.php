<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Integration;

use HeyFrame\Core\Framework\Api\Acl\Admin\Role\AclRoleDefinition;
use HeyFrame\Core\Framework\App\AppDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\PasswordField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Integration\Aggregate\IntegrationRole\IntegrationRoleDefinition;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineHistory\StateMachineHistoryDefinition;

#[Package('fundamentals@framework')]
class IntegrationDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'integration';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return IntegrationCollection::class;
    }

    public function getEntityClass(): string
    {
        return IntegrationEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'admin' => false,
        ];
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('label', 'label'))->addFlags(new Required()),
            (new StringField('access_key', 'accessKey'))->addFlags(new Required()),
            (new PasswordField('secret_access_key', 'secretAccessKey'))->addFlags(new Required()),
            new DateTimeField('last_usage_at', 'lastUsageAt'),
            new BoolField('admin', 'admin'),
            new CustomFields(),
            new DateTimeField('deleted_at', 'deletedAt'),

            (new OneToOneAssociationField('app', 'id', 'integration_id', AppDefinition::class, false))->addFlags(new RestrictDelete()),
            new OneToManyAssociationField('stateMachineHistoryEntries', StateMachineHistoryDefinition::class, 'integration_id', 'id'),
            new ManyToManyAssociationField('aclRoles', AclRoleDefinition::class, IntegrationRoleDefinition::class, 'integration_id', 'acl_role_id'),
        ]);
    }
}

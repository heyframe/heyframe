<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Notification;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityProtection\EntityProtectionCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityProtection\ReadProtection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityProtection\WriteProtection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ListField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Integration\IntegrationDefinition;
use HeyFrame\Core\System\User\UserDefinition;

#[Package('framework')]
class NotificationDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'notification';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return NotificationCollection::class;
    }

    public function getEntityClass(): string
    {
        return NotificationEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'requiredPrivileges' => [],
            'adminOnly' => false,
        ];
    }

    public function since(): ?string
    {
        return '6.4.7.0';
    }

    protected function defineProtections(): EntityProtectionCollection
    {
        return new EntityProtectionCollection([
            new ReadProtection(Context::SYSTEM_SCOPE),
            new WriteProtection(Context::SYSTEM_SCOPE),
        ]);
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('status', 'status'))->addFlags(new Required()),
            (new LongTextField('message', 'message'))->addFlags(new Required()),
            new BoolField('admin_only', 'adminOnly'),
            new ListField('required_privileges', 'requiredPrivileges'),

            new FkField('created_by_integration_id', 'createdByIntegrationId', IntegrationDefinition::class),
            new FkField('created_by_user_id', 'createdByUserId', UserDefinition::class),

            new ManyToOneAssociationField('createdByIntegration', 'created_by_integration_id', IntegrationDefinition::class, 'id', false),
            new ManyToOneAssociationField('createdByUser', 'created_by_user_id', UserDefinition::class, 'id', false),
        ]);
    }
}

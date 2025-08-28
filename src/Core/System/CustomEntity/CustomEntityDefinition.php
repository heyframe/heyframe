<?php declare(strict_types=1);

namespace HeyFrame\Core\System\CustomEntity;

use HeyFrame\Core\Framework\App\AppDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityProtection\EntityProtectionCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityProtection\WriteProtection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\PluginDefinition;

#[Package('framework')]
class CustomEntityDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'custom_entity';

    public function getCollectionClass(): string
    {
        return CustomEntityCollection::class;
    }

    public function getEntityClass(): string
    {
        return CustomEntityEntity::class;
    }

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function since(): ?string
    {
        return '6.4.9.0';
    }

    protected function defineProtections(): EntityProtectionCollection
    {
        return new EntityProtectionCollection([
            new WriteProtection(Context::SYSTEM_SCOPE),
        ]);
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('name', 'name'))->addFlags(new Required()),
            (new JsonField('fields', 'fields'))->addFlags(new Required()),
            new JsonField('flags', 'flags'),
            new FkField('app_id', 'appId', AppDefinition::class),
            new FkField('plugin_id', 'pluginId', PluginDefinition::class),
            (new BoolField('cms_aware', 'cmsAware'))->addFlags(new Runtime()),
            (new BoolField('store_api_aware', 'storeApiAware'))->addFlags(new Runtime()),
            new BoolField('custom_fields_aware', 'customFieldsAware'),
            new StringField('label_property', 'labelProperty'),
            new DateTimeField('deleted_at', 'deletedAt'),
        ]);
    }
}

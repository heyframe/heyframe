<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\DataAbstractionLayer\Field\TestDefinition;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * @internal
 */
class WriteProtectedDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = '_test_nullable';

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
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new Required(), new PrimaryKey()),
            (new StringField('protected', 'protected'))->addFlags(new ApiAware(), new WriteProtected()),
            (new StringField('system_protected', 'systemProtected'))->addFlags(new ApiAware(), new WriteProtected(Context::SYSTEM_SCOPE)),
            new FkField('relation_id', 'relationId', WriteProtectedRelationDefinition::class),
            (new ManyToOneAssociationField('relation', 'relation_id', WriteProtectedRelationDefinition::class, 'id', false))->addFlags(new ApiAware(), new WriteProtected()),
            (new ManyToManyAssociationField('relations', WriteProtectedRelationDefinition::class, WriteProtectedReferenceDefinition::class, 'wp_id', 'relation_id'))->addFlags(new ApiAware(), new WriteProtected()),
            new FkField('system_relation_id', 'systemRelationId', WriteProtectedRelationDefinition::class),
            (new ManyToOneAssociationField('systemRelation', 'system_relation_id', WriteProtectedRelationDefinition::class, 'id', false))->addFlags(new ApiAware(), new WriteProtected(Context::SYSTEM_SCOPE)),
            (new ManyToManyAssociationField('systemRelations', WriteProtectedRelationDefinition::class, WriteProtectedReferenceDefinition::class, 'wp_id', 'relation_id'))->addFlags(new ApiAware(), new WriteProtected(Context::SYSTEM_SCOPE)),
        ]);
    }

    protected function defaultFields(): array
    {
        return [];
    }
}

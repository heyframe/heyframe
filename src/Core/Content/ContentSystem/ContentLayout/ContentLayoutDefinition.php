<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\ContentLayout;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class ContentLayoutDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'content_layout';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ContentLayoutEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ContentLayoutCollection::class;
    }

    public function since(): ?string
    {
        return '6.7.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new StringField('name', 'name', 255))->addFlags(new ApiAware(), new Required()),
            (new StringField('version', 'version', 20))->addFlags(new ApiAware(), new Required()),
            (new JsonField('structure', 'structure'))->addFlags(new ApiAware(), new Required()),
            (new JsonField('schema', 'schema'))->addFlags(new ApiAware()),
        ]);
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\ContentLayoutAssignment;

use HeyFrame\Core\Content\ContentSystem\ContentLayout\ContentLayoutDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelDefinition;

#[Package('discovery')]
class ContentLayoutAssignmentDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'content_layout_assignment';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ContentLayoutAssignmentEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ContentLayoutAssignmentCollection::class;
    }

    public function since(): ?string
    {
        return '6.7.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new StringField('entity_type', 'entityType', 50))->addFlags(new ApiAware()),
            (new IdField('entity_id', 'entityId'))->addFlags(new ApiAware()),
            (new FkField('channel_id', 'channelId', ChannelDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FkField('layout_id', 'layoutId', ContentLayoutDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new ManyToOneAssociationField('layout', 'layout_id', ContentLayoutDefinition::class, 'id', false))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('channel', 'channel_id', ChannelDefinition::class, 'id', false))->addFlags(new ApiAware()),
        ]);
    }
}

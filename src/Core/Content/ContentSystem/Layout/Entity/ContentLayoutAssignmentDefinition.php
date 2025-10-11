<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Layout\Entity;

use HeyFrame\Core\Content\ContentSystem\Routing\Entity\ContentRouteDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelDefinition;

/**
 * @internal
 */
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
            (new FkField('route_id', 'routeId', ContentRouteDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new StringField('entity_type', 'entityType', 50))->addFlags(new ApiAware()),
            (new IdField('entity_id', 'entityId'))->addFlags(new ApiAware()),
            (new StringField('association_path', 'associationPath', 255))->addFlags(new ApiAware()),
            (new FkField('channel_id', 'salesChannelId', ChannelDefinition::class))->addFlags(new ApiAware()),
            (new FkField('layout_id', 'layoutId', ContentLayoutDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new IntField('priority', 'priority'))->addFlags(new ApiAware()),

            (new ManyToOneAssociationField('route', 'route_id', ContentRouteDefinition::class, 'id', false))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('layout', 'layout_id', ContentLayoutDefinition::class, 'id', false))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('salesChannel', 'channel_id', ChannelDefinition::class, 'id', false))->addFlags(new ApiAware()),
        ]);
    }
}

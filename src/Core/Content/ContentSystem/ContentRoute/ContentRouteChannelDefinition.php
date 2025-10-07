<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\ContentRoute;

use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelDefinition;

#[Package('discovery')]
class ContentRouteChannelDefinition extends MappingEntityDefinition
{
    final public const ENTITY_NAME = 'content_route_channel';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function since(): ?string
    {
        return '6.7.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('content_route_id', 'contentRouteId', ContentRouteDefinition::class))
                ->addFlags(new PrimaryKey(), new Required()),
            (new FkField('channel_id', 'channelId', ChannelDefinition::class))
                ->addFlags(new PrimaryKey(), new Required()),

            new ManyToOneAssociationField('contentRoute', 'content_route_id', ContentRouteDefinition::class, 'id', false),
            new ManyToOneAssociationField('channel', 'channel_id', ChannelDefinition::class, 'id', false),

            new CreatedAtField(),
        ]);
    }
}

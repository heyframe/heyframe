<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\ContentRoute;

use HeyFrame\Core\Content\ContentSystem\ContentLayout\ContentLayoutDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelDefinition;

#[Package('discovery')]
class ContentRouteDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'content_route';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ContentRouteEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ContentRouteCollection::class;
    }

    public function since(): ?string
    {
        return '6.7.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new StringField('name', 'name'))->addFlags(new ApiAware(), new Required()),
            (new StringField('url_pattern', 'urlPattern'))->addFlags(new ApiAware(), new Required()),
            (new JsonField('parameter_binding', 'parameterBinding'))->addFlags(new ApiAware(), new Required()),
            (new FkField('layout_id', 'layoutId', ContentLayoutDefinition::class))->addFlags(new ApiAware()),
            (new JsonField('layout_cascade', 'layoutCascade'))->addFlags(new ApiAware()),
            (new IntField('priority', 'priority'))->addFlags(new ApiAware()),
            (new JsonField('overrides', 'overrides'))->addFlags(new ApiAware()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),

            (new ManyToOneAssociationField('layout', 'layout_id', ContentLayoutDefinition::class, 'id', false))->addFlags(new ApiAware()),

            (new ManyToManyAssociationField(
                'channels',
                ChannelDefinition::class,
                ContentRouteChannelDefinition::class,
                'content_route_id',
                'channel_id'
            ))->addFlags(new ApiAware()),
        ]);
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Routing\Entity;

use HeyFrame\Core\Content\ContentSystem\Layout\Entity\ContentLayoutAssignmentDefinition;
use HeyFrame\Core\Framework\Api\Context\AdminApiSource;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
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
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('name', 'name'))->addFlags(new ApiAware(), new Required()),
            (new StringField('url_pattern', 'urlPattern'))->addFlags(new ApiAware(), new Required()),
            (new JsonField('parameter_binding', 'parameterBinding'))->addFlags(new ApiAware(AdminApiSource::class), new Required()),
            (new IntField('priority', 'priority'))->addFlags(new ApiAware(AdminApiSource::class)),
            (new JsonField('overrides', 'overrides'))->addFlags(new ApiAware(AdminApiSource::class)),
            (new BoolField('active', 'active'))->addFlags(new ApiAware(AdminApiSource::class)),

            (new OneToManyAssociationField('layoutAssignments', ContentLayoutAssignmentDefinition::class, 'route_id'))->addFlags(new ApiAware(AdminApiSource::class)),
        ]);
    }
}

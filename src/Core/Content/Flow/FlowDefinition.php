<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow;

use HeyFrame\Core\Content\Flow\Aggregate\FlowSequence\FlowSequenceDefinition;
use HeyFrame\Core\Framework\App\Aggregate\FlowEvent\AppFlowEventDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BlobField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

class FlowDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'flow';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return FlowCollection::class;
    }

    public function getEntityClass(): string
    {
        return FlowEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'active' => false,
            'priority' => 1,
        ];
    }

    public function since(): ?string
    {
        return '6.4.6.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('name', 'name', 255))->addFlags(new Required()),
            (new StringField('event_name', 'eventName', 255))->addFlags(new Required()),
            new IntField('priority', 'priority'),
            (new BlobField('payload', 'payload'))->removeFlag(ApiAware::class)->addFlags(new WriteProtected(Context::SYSTEM_SCOPE)),
            (new BoolField('invalid', 'invalid'))->addFlags(new WriteProtected(Context::SYSTEM_SCOPE)),
            new BoolField('active', 'active'),
            new StringField('description', 'description', 500),
            (new OneToManyAssociationField('sequences', FlowSequenceDefinition::class, 'flow_id', 'id'))->addFlags(new CascadeDelete()),
            new CustomFields(),
            new FkField('app_flow_event_id', 'appFlowEventId', AppFlowEventDefinition::class),
            new ManyToOneAssociationField('appFlowEvent', 'app_flow_event_id', AppFlowEventDefinition::class, 'id', false),
        ]);
    }
}

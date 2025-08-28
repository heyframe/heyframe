<?php declare(strict_types=1);

namespace HeyFrame\Core\System\StateMachine\Aggregation\StateMachineHistory;

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
use HeyFrame\Core\System\Integration\IntegrationDefinition;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateDefinition;
use HeyFrame\Core\System\StateMachine\StateMachineDefinition;
use HeyFrame\Core\System\User\UserDefinition;

#[Package('checkout')]
class StateMachineHistoryDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'state_machine_history';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return StateMachineHistoryEntity::class;
    }

    public function getCollectionClass(): string
    {
        return StateMachineHistoryCollection::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new IdField('referenced_id', 'referencedId'))->addFlags(new Required()),
            (new IdField('referenced_version_id', 'referencedVersionId'))->addFlags(new Required()),

            (new FkField('state_machine_id', 'stateMachineId', StateMachineDefinition::class))->addFlags(new Required()),
            new ManyToOneAssociationField('stateMachine', 'state_machine_id', StateMachineDefinition::class, 'id', false),

            (new StringField('entity_name', 'entityName'))->addFlags(new Required()),

            (new FkField('from_state_id', 'fromStateId', StateMachineStateDefinition::class))->addFlags(new Required()),
            (new ManyToOneAssociationField('fromStateMachineState', 'from_state_id', StateMachineStateDefinition::class, 'id', false))->addFlags(new ApiAware()),

            (new FkField('to_state_id', 'toStateId', StateMachineStateDefinition::class))->addFlags(new Required()),
            (new ManyToOneAssociationField('toStateMachineState', 'to_state_id', StateMachineStateDefinition::class, 'id', false))->addFlags(new ApiAware()),
            new StringField('action_name', 'transitionActionName'),
            new FkField('user_id', 'userId', UserDefinition::class),
            new FkField('integration_id', 'integrationId', IntegrationDefinition::class),

            new ManyToOneAssociationField('user', 'user_id', UserDefinition::class, 'id', false),
            new ManyToOneAssociationField('integration', 'integration_id', IntegrationDefinition::class, 'id', false),
        ]);
    }
}

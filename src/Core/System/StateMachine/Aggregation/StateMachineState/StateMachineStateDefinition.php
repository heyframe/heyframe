<?php declare(strict_types=1);

namespace HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState;

use HeyFrame\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransactionCapture\OrderTransactionCaptureDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransactionCaptureRefund\OrderTransactionCaptureRefundDefinition;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineHistory\StateMachineHistoryDefinition;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionDefinition;
use HeyFrame\Core\System\StateMachine\StateMachineDefinition;

#[Package('checkout')]
class StateMachineStateDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'state_machine_state';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return StateMachineStateEntity::class;
    }

    public function getCollectionClass(): string
    {
        return StateMachineStateCollection::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

            (new StringField('technical_name', 'technicalName'))->addFlags(new ApiAware(), new Required(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),
            (new TranslatedField('name'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),

            (new FkField('state_machine_id', 'stateMachineId', StateMachineDefinition::class))->addFlags(new Required()),
            new ManyToOneAssociationField('stateMachine', 'state_machine_id', StateMachineDefinition::class, 'id', false),
            new OneToManyAssociationField('fromStateMachineTransitions', StateMachineTransitionDefinition::class, 'from_state_id'),
            new OneToManyAssociationField('toStateMachineTransitions', StateMachineTransitionDefinition::class, 'to_state_id'),

            (new TranslationsAssociationField(StateMachineStateTranslationDefinition::class, 'state_machine_state_id'))->addFlags(new Required(), new CascadeDelete()),
            new OneToManyAssociationField('orderTransactions', OrderTransactionDefinition::class, 'state_id'),
            new OneToManyAssociationField('orderDeliveries', OrderDeliveryDefinition::class, 'state_id'),
            new OneToManyAssociationField('orders', OrderDefinition::class, 'state_id'),
            new OneToManyAssociationField('orderTransactionCaptures', OrderTransactionCaptureDefinition::class, 'state_id'),
            new OneToManyAssociationField('orderTransactionCaptureRefunds', OrderTransactionCaptureRefundDefinition::class, 'state_id'),
            (new TranslatedField('customFields'))->addFlags(new ApiAware()),
            new OneToManyAssociationField('toStateMachineHistoryEntries', StateMachineHistoryDefinition::class, 'to_state_id'),
            new OneToManyAssociationField('fromStateMachineHistoryEntries', StateMachineHistoryDefinition::class, 'from_state_id'),
        ]);
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Aggregate\OrderTransactionCapture;

use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransactionCaptureRefund\OrderTransactionCaptureRefundDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CalculatedPriceField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StateMachineStateField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\VersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateDefinition;

#[Package('checkout')]
class OrderTransactionCaptureDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'order_transaction_capture';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function since(): ?string
    {
        return '6.4.12.0';
    }

    public function getEntityClass(): string
    {
        return OrderTransactionCaptureEntity::class;
    }

    public function getCollectionClass(): string
    {
        return OrderTransactionCaptureCollection::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return OrderTransactionDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new VersionField())->addFlags(new ApiAware()),
            (new FkField('order_transaction_id', 'orderTransactionId', OrderTransactionDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(OrderTransactionDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new StateMachineStateField('state_id', 'stateId', OrderTransactionCaptureStates::STATE_MACHINE))->addFlags(new ApiAware(), new Required()),
            (new ManyToOneAssociationField('stateMachineState', 'state_id', StateMachineStateDefinition::class, 'id'))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('transaction', 'order_transaction_id', OrderTransactionDefinition::class, 'id', false))->addFlags(new ApiAware()),
            (new OneToManyAssociationField('refunds', OrderTransactionCaptureRefundDefinition::class, 'capture_id'))->addFlags(new ApiAware(), new CascadeDelete()),

            (new StringField('external_reference', 'externalReference'))->addFlags(new ApiAware()),
            (new CalculatedPriceField('amount', 'amount'))->addFlags(new ApiAware(), new Required()),
            (new CustomFields())->addFlags(new ApiAware()),
        ]);
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Aggregate\OrderDelivery;

use HeyFrame\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressDefinition;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderDeliveryPosition\OrderDeliveryPositionDefinition;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CalculatedPriceField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ListField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StateMachineStateField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\VersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateDefinition;

#[Package('checkout')]
class OrderDeliveryDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'order_delivery';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return OrderDeliveryCollection::class;
    }

    public function getEntityClass(): string
    {
        return OrderDeliveryEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'trackingCodes' => [],
        ];
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): ?string
    {
        return OrderDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new VersionField())->addFlags(new ApiAware()),

            (new FkField('order_id', 'orderId', OrderDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(OrderDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new FkField('shipping_order_address_id', 'shippingOrderAddressId', OrderAddressDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(OrderAddressDefinition::class, 'shipping_order_address_version_id'))->addFlags(new ApiAware(), new Required()),

            (new FkField('shipping_method_id', 'shippingMethodId', ShippingMethodDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new StateMachineStateField('state_id', 'stateId', OrderDeliveryStates::STATE_MACHINE))->addFlags(new ApiAware(), new Required()),
            (new ManyToOneAssociationField('stateMachineState', 'state_id', StateMachineStateDefinition::class, 'id'))->addFlags(new ApiAware()),

            (new ListField('tracking_codes', 'trackingCodes', StringField::class))->addFlags(new ApiAware(), new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new DateTimeField('shipping_date_earliest', 'shippingDateEarliest'))->addFlags(new ApiAware(), new Required(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),
            (new DateTimeField('shipping_date_latest', 'shippingDateLatest'))->addFlags(new ApiAware(), new Required(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),
            (new CalculatedPriceField('shipping_costs', 'shippingCosts'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware()),
            new ManyToOneAssociationField('order', 'order_id', OrderDefinition::class, 'id', false),
            (new ManyToOneAssociationField('shippingOrderAddress', 'shipping_order_address_id', OrderAddressDefinition::class, 'id'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::ASSOCIATION_SEARCH_RANKING)),
            (new ManyToOneAssociationField('shippingMethod', 'shipping_method_id', ShippingMethodDefinition::class, 'id'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::ASSOCIATION_SEARCH_RANKING)),
            (new OneToManyAssociationField('positions', OrderDeliveryPositionDefinition::class, 'order_delivery_id', 'id'))->addFlags(new ApiAware(), new CascadeDelete(), new SearchRanking(SearchRanking::ASSOCIATION_SEARCH_RANKING)),
            new OneToOneAssociationField('primaryOrder', 'id', 'primary_order_delivery_id', OrderDefinition::class, false),
        ]);
    }
}

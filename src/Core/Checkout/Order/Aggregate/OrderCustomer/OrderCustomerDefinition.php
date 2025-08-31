<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Aggregate\OrderCustomer;

use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\VersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class OrderCustomerDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'order_customer';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return OrderCustomerCollection::class;
    }

    public function getEntityClass(): string
    {
        return OrderCustomerEntity::class;
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
            new FkField('customer_id', 'customerId', CustomerDefinition::class),

            (new FkField('order_id', 'orderId', OrderDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(OrderDefinition::class))->addFlags(new Required()),

            (new StringField('email', 'email'))->addFlags(new ApiAware(), new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new StringField('name', 'name'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::LOW_SEARCH_RANKING)),
            (new StringField('nickname', 'nickname'))->addFlags(new ApiAware(), new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new StringField('customer_number', 'customerNumber'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new CustomFields())->addFlags(new ApiAware()),
            new OneToOneAssociationField('order', 'order_id', 'id', OrderDefinition::class, false),
            (new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class, 'id', false))->addFlags(new SearchRanking(0.5)),
        ]);
    }
}

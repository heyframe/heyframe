<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Aggregate\CustomerMemberships;

use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\MembershipPlans\MembershipPlansDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ExtraFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class CustomerMembershipsDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'customer_memberships';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return CustomerMembershipsCollection::class;
    }

    public function getEntityClass(): string
    {
        return CustomerMembershipsEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('customer_id', 'customerId', CustomerDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FkField('membership_plans_id', 'membershipPlansId', MembershipPlansDefinition::class))->addFlags(new ApiAware()),
            (new DateTimeField('start_at', 'startAt'))->addFlags(new ApiAware()),
            (new DateTimeField('end_at', 'endAt'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware()),
            (new ExtraFields())->addFlags(new ApiAware()),
            new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class, 'id', false),
        ]);
    }
}

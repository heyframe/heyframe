<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\MembershipPlans;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('after-sales')]
class MembershipPlansDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'membership_plans';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return MembershipPlansCollection::class;
    }

    public function getEntityClass(): string
    {
        return MembershipPlansEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new StringField('technical_name', 'technicalName'))->addFlags(new ApiAware(), new Required()),
            new JsonField('config', 'config', [], []),
            (new IntField('duration_days', 'durationDays'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware()),
            (new JsonField('privileges', 'privileges'))->addFlags(new ApiAware()),
        ]);
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Points\Aggregate\PointsLog;

use HeyFrame\Core\Checkout\Points\PointsDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ExtraFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\IgnoreInOpenapiSchema;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class PointsLogDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'points_log';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PointsLogCollection::class;
    }

    public function getEntityClass(): string
    {
        return PointsLogEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('points_id', 'pointsId', PointsDefinition::class))->addFlags(new Required(), new ApiAware()),
            (new StringField('type', 'type'))->addFlags(new ApiAware(), new Required(), new IgnoreInOpenapiSchema()),
            (new IntField('amount', 'amount'))->addFlags(new ApiAware(), new WriteProtected(Context::SYSTEM_SCOPE)),
            (new IntField('balance_after', 'balanceAfter'))->addFlags(new ApiAware(), new WriteProtected(Context::SYSTEM_SCOPE)),
            (new StringField('referenced_type', 'referencedType'))->addFlags(new ApiAware(), new IgnoreInOpenapiSchema()),
            (new StringField('referenced_id', 'referencedId'))->addFlags(new ApiAware(), new IgnoreInOpenapiSchema()),
            (new CustomFields())->addFlags(new ApiAware()),
            (new ExtraFields())->addFlags(new ApiAware()),
            new ManyToOneAssociationField('points', 'points_id', PointsDefinition::class, 'id', false),
        ]);
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Points\Aggregate\PointsTransactions;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Points\PointsDefinition;

#[Package('checkout')]
class PointsTransactionsDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'points_transactions';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PointsTransactionsCollection::class;
    }

    public function getEntityClass(): string
    {
        return PointsTransactionsEntity::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return PointsDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('points_id', 'pointsId', PointsDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new StringField('tx_type', 'txType'))->addFlags(new ApiAware(), new Required()),
            (new IntField('points_amount', 'pointsAmount'))->addFlags(new ApiAware(), new WriteProtected()),
            (new IntField('points_after', 'pointsAfter'))->addFlags(new ApiAware(), new Required(), new WriteProtected()),
            (new StringField('referenced_id', 'referencedId'))->addFlags(new ApiAware()),
            (new StringField('reference_type', 'referenceType'))->addFlags(new ApiAware()),
            new ManyToOneAssociationField('points', 'points_id', PointsDefinition::class, 'id', false),
        ]);
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionSetGroup;

use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionSetGroupRule\PromotionSetGroupRuleDefinition;
use HeyFrame\Core\Checkout\Promotion\PromotionDefinition;
use HeyFrame\Core\Content\Rule\RuleDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FloatField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class PromotionSetGroupDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'promotion_setgroup';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return PromotionSetGroupEntity::class;
    }

    public function getCollectionClass(): string
    {
        return PromotionSetGroupCollection::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): ?string
    {
        return PromotionDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('promotion_id', 'promotionId', PromotionDefinition::class, 'id'))->addFlags(new Required()),
            (new StringField('packager_key', 'packagerKey'))->addFlags(new Required()),
            (new StringField('sorter_key', 'sorterKey', 32))->addFlags(new Required()),
            (new FloatField('value', 'value'))->addFlags(new Required()),
            new ManyToOneAssociationField('promotion', 'promotion_id', PromotionDefinition::class, 'id'),
            (new ManyToManyAssociationField('setGroupRules', RuleDefinition::class, PromotionSetGroupRuleDefinition::class, 'setgroup_id', 'rule_id'))->addFlags(new CascadeDelete()),
        ]);
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionDiscountRule;

use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountDefinition;
use HeyFrame\Core\Content\Rule\RuleDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class PromotionDiscountRuleDefinition extends MappingEntityDefinition
{
    final public const ENTITY_NAME = 'promotion_discount_rule';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('discount_id', 'discountId', PromotionDiscountDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('rule_id', 'ruleId', RuleDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('discount', 'discount_id', PromotionDiscountDefinition::class, 'id'),
            new ManyToOneAssociationField('rule', 'rule_id', RuleDefinition::class, 'id'),
        ]);
    }
}

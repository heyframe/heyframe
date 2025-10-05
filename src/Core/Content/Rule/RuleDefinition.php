<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Rule;

use HeyFrame\Core\Checkout\Payment\PaymentMethodDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionCartRule\PromotionCartRuleDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionDiscountRule\PromotionDiscountRuleDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionOrderRule\PromotionOrderRuleDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionPersonaRule\PromotionPersonaRuleDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionSetGroup\PromotionSetGroupDefinition;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionSetGroupRule\PromotionSetGroupRuleDefinition;
use HeyFrame\Core\Checkout\Promotion\PromotionDefinition;
use HeyFrame\Core\Content\Flow\Aggregate\FlowSequence\FlowSequenceDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductPrice\ProductPriceDefinition;
use HeyFrame\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionDefinition;
use HeyFrame\Core\Content\Rule\Aggregate\RuleTag\RuleTagDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BlobField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\RuleAreas;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Since;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ListField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Tag\TagDefinition;

#[Package('fundamentals@after-sales')]
class RuleDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'rule';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return RuleCollection::class;
    }

    public function getEntityClass(): string
    {
        return RuleEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('name', 'name'))->addFlags(new ApiAware(), new Required()),
            (new IntField('priority', 'priority'))->addFlags(new Required()),
            (new LongTextField('description', 'description'))->addFlags(new ApiAware()),
            (new BlobField('payload', 'payload'))->removeFlag(ApiAware::class)->addFlags(new WriteProtected(Context::SYSTEM_SCOPE)),
            (new BoolField('invalid', 'invalid'))->addFlags(new WriteProtected(Context::SYSTEM_SCOPE)),
            (new ListField('areas', 'areas'))->addFlags(new WriteProtected(Context::SYSTEM_SCOPE)),
            (new CustomFields())->addFlags(new ApiAware()),
            new JsonField('module_types', 'moduleTypes'),

            (new OneToManyAssociationField('conditions', RuleConditionDefinition::class, 'rule_id', 'id'))->addFlags(new CascadeDelete()),

            // Reverse Associations not available in sales-channel-api
            (new OneToManyAssociationField('productPrices', ProductPriceDefinition::class, 'rule_id', 'id'))->addFlags(new RestrictDelete(), new RuleAreas(RuleAreas::PRODUCT_AREA)),
            (new OneToManyAssociationField('paymentMethods', PaymentMethodDefinition::class, 'availability_rule_id', 'id'))->addFlags(new RestrictDelete(), new RuleAreas(RuleAreas::PAYMENT_AREA)),
            (new OneToManyAssociationField('personaPromotions', PromotionDefinition::class, 'persona_rule_id', 'id'))->addFlags(new RestrictDelete(), new RuleAreas(RuleAreas::PROMOTION_AREA)),
            (new OneToManyAssociationField('flowSequences', FlowSequenceDefinition::class, 'rule_id', 'id'))->addFlags(new RestrictDelete(), new RuleAreas(RuleAreas::FLOW_AREA)),

            new ManyToManyAssociationField('tags', TagDefinition::class, RuleTagDefinition::class, 'rule_id', 'tag_id'),

            // Promotion References
            (new ManyToManyAssociationField('personaPromotions', PromotionDefinition::class, PromotionPersonaRuleDefinition::class, 'rule_id', 'promotion_id'))->addFlags(new RestrictDelete(), new RuleAreas(RuleAreas::PROMOTION_AREA)),
            (new ManyToManyAssociationField('orderPromotions', PromotionDefinition::class, PromotionOrderRuleDefinition::class, 'rule_id', 'promotion_id'))->addFlags(new RestrictDelete(), new RuleAreas(RuleAreas::PROMOTION_AREA)),
            (new ManyToManyAssociationField('cartPromotions', PromotionDefinition::class, PromotionCartRuleDefinition::class, 'rule_id', 'promotion_id'))->addFlags(new RestrictDelete(), new RuleAreas(RuleAreas::PROMOTION_AREA)),
            (new ManyToManyAssociationField('promotionDiscounts', PromotionDiscountDefinition::class, PromotionDiscountRuleDefinition::class, 'rule_id', 'discount_id'))->addFlags(new RestrictDelete(), new RuleAreas(RuleAreas::PROMOTION_AREA)),
            (new ManyToManyAssociationField('promotionSetGroups', PromotionSetGroupDefinition::class, PromotionSetGroupRuleDefinition::class, 'rule_id', 'setgroup_id'))->addFlags(new RestrictDelete(), new RuleAreas(RuleAreas::PROMOTION_AREA)),
        ]);
    }
}

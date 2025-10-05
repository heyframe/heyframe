<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Rule;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\Rule\CartRuleScope;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemScope;
use HeyFrame\Core\Checkout\Promotion\Cart\PromotionItemBuilder;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\RuleComparison;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;

#[Package('fundamentals@after-sales')]
class PromotionCodeOfTypeRule extends Rule
{
    final public const RULE_NAME = 'promotionCodeOfType';

    /**
     * @internal
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        protected ?string $promotionCodeType = null
    ) {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        if ($scope instanceof LineItemScope) {
            return $this->lineItemMatches($scope->getLineItem());
        }

        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        $promotionLineItems = $scope->getCart()->getLineItems()->filterFlatByType(LineItem::PROMOTION_LINE_ITEM_TYPE);
        $hasNoPromotionLineItems = \count($promotionLineItems) === 0;

        if ($this->operator === self::OPERATOR_EQ && $hasNoPromotionLineItems) {
            return false;
        }

        if ($this->operator === self::OPERATOR_NEQ && $hasNoPromotionLineItems) {
            return true;
        }

        foreach ($promotionLineItems as $lineItem) {
            if ($lineItem->getPayloadValue('promotionCodeType') === null) {
                continue;
            }

            if ($this->lineItemMatches($lineItem)) {
                return true;
            }
        }

        return false;
    }

    public function getConstraints(): array
    {
        return [
            'promotionCodeType' => RuleConstraints::string(),
            'operator' => RuleConstraints::stringOperators(false),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_STRING)
            ->selectField('promotionCodeType', [
                PromotionItemBuilder::PROMOTION_TYPE_GLOBAL,
                PromotionItemBuilder::PROMOTION_TYPE_INDIVIDUAL,
                PromotionItemBuilder::PROMOTION_TYPE_FIXED,
            ]);
    }

    private function lineItemMatches(LineItem $lineItem): bool
    {
        if ($this->promotionCodeType === null) {
            return false;
        }

        $promotionCodeType = $lineItem->getPayloadValue('promotionCodeType');

        return RuleComparison::string($promotionCodeType, $this->promotionCodeType, $this->operator);
    }
}

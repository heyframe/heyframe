<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\RuleComparison;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;

#[Package('fundamentals@after-sales')]
class LineItemOfTypeRule extends Rule
{
    final public const RULE_NAME = 'cartLineItemOfType';

    protected string $lineItemType;

    /**
     * @internal
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        ?string $lineItemType = null
    ) {
        parent::__construct();
        $this->lineItemType = (string) $lineItemType;
    }

    public function match(RuleScope $scope): bool
    {
        if ($scope instanceof LineItemScope) {
            return $this->lineItemMatches($scope->getLineItem());
        }

        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        foreach ($scope->getCart()->getLineItems()->getFlat() as $lineItem) {
            if ($this->lineItemMatches($lineItem)) {
                return true;
            }
        }

        return false;
    }

    public function getConstraints(): array
    {
        return [
            'lineItemType' => RuleConstraints::string(),
            'operator' => RuleConstraints::stringOperators(false),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_STRING)
            ->selectField('lineItemType', [LineItem::PRODUCT_LINE_ITEM_TYPE, LineItem::PROMOTION_LINE_ITEM_TYPE]);
    }

    private function lineItemMatches(LineItem $lineItem): bool
    {
        return RuleComparison::string($lineItem->getType(), $this->lineItemType, $this->operator);
    }
}

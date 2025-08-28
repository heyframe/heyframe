<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Rule;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Checkout\Cart\Rule\CartRuleScope;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemScope;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Container\FilterRule;
use HeyFrame\Core\Framework\Rule\Exception\UnsupportedOperatorException;
use HeyFrame\Core\Framework\Rule\RuleComparison;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class PromotionValueRule extends FilterRule
{
    final public const RULE_NAME = 'promotionValue';

    protected float $amount;

    /**
     * @internal
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        ?float $amount = null
    ) {
        parent::__construct();
        $this->amount = (float) $amount;
    }

    /**
     * @throws UnsupportedOperatorException
     */
    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        $promotions = new LineItemCollection($scope->getCart()->getLineItems()->filterFlatByType(LineItem::PROMOTION_LINE_ITEM_TYPE));
        $filter = $this->filter;
        if ($filter !== null) {
            $context = $scope->getChannelContext();

            $promotions = $promotions->filter(static function (LineItem $lineItem) use ($filter, $context) {
                $scope = new LineItemScope($lineItem, $context);

                return $filter->match($scope);
            });
        }

        $promotionAmount = $promotions->getPrices()->getTotalPriceAmount() * -1;

        return RuleComparison::numeric($promotionAmount, $this->amount, $this->operator);
    }

    public function getConstraints(): array
    {
        return [
            'amount' => RuleConstraints::float(),
            'operator' => RuleConstraints::numericOperators(false),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_NUMBER)
            ->numberField('amount');
    }
}

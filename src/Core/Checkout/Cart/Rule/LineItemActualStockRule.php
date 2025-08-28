<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Exception\UnsupportedOperatorException;
use HeyFrame\Core\Framework\Rule\Exception\UnsupportedValueException;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\RuleComparison;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class LineItemActualStockRule extends Rule
{
    final public const RULE_NAME = 'cartLineItemActualStock';

    /**
     * @internal
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        protected ?int $stock = null
    ) {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        if ($scope instanceof LineItemScope) {
            return $this->matchStock($scope->getLineItem());
        }

        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        foreach ($scope->getCart()->getLineItems()->filterGoodsFlat() as $lineItem) {
            if ($this->matchStock($lineItem)) {
                return true;
            }
        }

        return false;
    }

    public function getConstraints(): array
    {
        return [
            'operator' => RuleConstraints::numericOperators(false),
            'stock' => RuleConstraints::int(),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_NUMBER)
            ->intField('stock');
    }

    /**
     * @throws UnsupportedOperatorException|UnsupportedValueException|CartException
     */
    private function matchStock(LineItem $lineItem): bool
    {
        if ($this->stock === null) {
            if (!Feature::isActive('v6.8.0.0')) {
                // @phpstan-ignore-next-line
                throw new UnsupportedValueException(\gettype($this->stock), self::class);
            }
            throw CartException::unsupportedValue(\gettype($this->stock), self::class);
        }

        $actualStock = $lineItem->getPayloadValue('stock');
        if ($actualStock === null) {
            return false;
        }

        return RuleComparison::numeric($actualStock, $this->stock, $this->operator);
    }
}

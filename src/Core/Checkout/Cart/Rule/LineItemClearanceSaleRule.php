<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class LineItemClearanceSaleRule extends Rule
{
    final public const RULE_NAME = 'cartLineItemClearanceSale';

    /**
     * @internal
     */
    public function __construct(protected bool $clearanceSale = false)
    {
        parent::__construct();
    }

    /**
     * @throws CartException
     */
    public function match(RuleScope $scope): bool
    {
        if ($scope instanceof LineItemScope) {
            return $this->matchesClearanceSaleCondition($scope->getLineItem());
        }

        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        foreach ($scope->getCart()->getLineItems()->filterGoodsFlat() as $lineItem) {
            if ($this->matchesClearanceSaleCondition($lineItem)) {
                return true;
            }
        }

        return false;
    }

    public function getConstraints(): array
    {
        return [
            'clearanceSale' => RuleConstraints::bool(),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->booleanField('clearanceSale');
    }

    /**
     * @throws CartException
     */
    private function matchesClearanceSaleCondition(LineItem $lineItem): bool
    {
        return (bool) $lineItem->getPayloadValue('isCloseout') === $this->clearanceSale;
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\RuleComparison;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class LineItemReleaseDateRule extends Rule
{
    final public const RULE_NAME = 'cartLineItemReleaseDate';

    /**
     * @internal
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        protected ?string $lineItemReleaseDate = null
    ) {
        parent::__construct();
    }

    public function getConstraints(): array
    {
        $constraints = [
            'operator' => RuleConstraints::datetimeOperators(),
        ];

        if ($this->operator === self::OPERATOR_EMPTY) {
            return $constraints;
        }

        $constraints['lineItemReleaseDate'] = RuleConstraints::datetime();

        return $constraints;
    }

    public function match(RuleScope $scope): bool
    {
        try {
            $ruleValue = $this->buildDate($this->lineItemReleaseDate);
        } catch (\Exception) {
            return false;
        }

        if ($scope instanceof LineItemScope) {
            return $this->matchesReleaseDate($scope->getLineItem(), $ruleValue);
        }

        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        foreach ($scope->getCart()->getLineItems()->filterGoodsFlat() as $lineItem) {
            if ($this->matchesReleaseDate($lineItem, $ruleValue)) {
                return true;
            }
        }

        return false;
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_NUMBER, true)
            ->dateTimeField('lineItemReleaseDate');
    }

    /**
     * @throws CartException
     */
    private function matchesReleaseDate(LineItem $lineItem, ?\DateTime $ruleValue): bool
    {
        try {
            $releasedAtString = $lineItem->getPayloadValue('releaseDate');

            if ($releasedAtString === null) {
                return RuleComparison::isNegativeOperator($this->operator);
            }

            /** @var \DateTime $itemReleased */
            $itemReleased = $this->buildDate($releasedAtString);
        } catch (\Exception) {
            return false;
        }

        if ($ruleValue === null) {
            return false;
        }

        return RuleComparison::datetime($itemReleased, $ruleValue, $this->operator);
    }

    /**
     * @throws \Exception
     */
    private function buildDate(?string $dateString): ?\DateTime
    {
        if ($dateString === null) {
            return null;
        }

        $dateTime = new \DateTime($dateString);

        return $dateTime;
    }
}

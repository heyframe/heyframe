<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
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
class LineItemVariantValueRule extends Rule
{
    public const RULE_NAME = 'cartLineItemVariantValue';

    /**
     * @param list<string>|null $identifiers
     *
     * @internal
     */
    public function __construct(
        public string $operator = Rule::OPERATOR_EQ,
        public ?array $identifiers = null
    ) {
        parent::__construct();
    }

    public function getConstraints(): array
    {
        return [
            'operator' => RuleConstraints::uuidOperators(false),
            'identifiers' => RuleConstraints::uuids(),
        ];
    }

    public function match(RuleScope $scope): bool
    {
        if ($scope instanceof LineItemScope) {
            return $this->matchLineItem($scope->getLineItem());
        }

        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        foreach ($scope->getCart()->getLineItems()->filterGoodsFlat() as $item) {
            if ($this->matchLineItem($item)) {
                return true;
            }
        }

        return false;
    }

    public function matchLineItem(LineItem $lineItem): bool
    {
        /**
         * @var list<string> $value
         */
        $value = $lineItem->getPayloadValue('optionIds') ?? [];

        return RuleComparison::uuids(
            $value,
            $this->identifiers,
            $this->operator
        );
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_STRING, false, true)
            ->entitySelectField('identifiers', PropertyGroupOptionDefinition::ENTITY_NAME, true);
    }
}

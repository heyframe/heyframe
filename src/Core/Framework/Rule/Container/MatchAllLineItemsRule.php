<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Rule\Container;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Checkout\Cart\Rule\CartRuleScope;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemScope;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\RuleScope;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @final
 *
 * MatchAllLineItemsRule returns true if all rules are true for all line items
 */
#[Package('fundamentals@after-sales')]
class MatchAllLineItemsRule extends Container
{
    final public const RULE_NAME = 'allLineItemsContainer';

    /**
     * @internal
     *
     * @param list<Rule> $rules
     * @param list<string> $types
     */
    public function __construct(
        array $rules = [],
        protected ?int $minimumShouldMatch = null,
        protected ?array $types = []
    ) {
        parent::__construct($rules);
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CartRuleScope && !$scope instanceof LineItemScope) {
            return false;
        }

        $lineItems = $scope instanceof LineItemScope
            ? new LineItemCollection([$scope->getLineItem()])
            : $scope->getCart()->getLineItems();

        if ($lineItems->count() === 0) {
            return false;
        }

        $flatItems = $this->filterAndFlatten($lineItems);

        if (\count($flatItems) === 0) {
            return false;
        }

        $context = $scope->getChannelContext();

        foreach ($this->rules as $rule) {
            $matched = 0;

            foreach ($flatItems as $lineItem) {
                $scope = new LineItemScope($lineItem, $context);
                $match = $rule->match($scope);

                if (!$this->minimumShouldMatch && !$match) {
                    return false;
                }

                if ($match) {
                    ++$matched;
                }
            }

            if ($this->minimumShouldMatch && $matched < $this->minimumShouldMatch) {
                return false;
            }
        }

        return true;
    }

    public function getConstraints(): array
    {
        $rules = parent::getConstraints();

        $rules['minimumShouldMatch'] = [new Type('int')];
        $rules['types'] = [new Type('array')];

        return $rules;
    }

    /**
     * @return array<LineItem>
     */
    private function filterAndFlatten(LineItemCollection $collection): array
    {
        $flat = $collection->getFlat();

        if (empty($this->types)) {
            return $flat;
        }

        return array_values(array_filter(
            $flat,
            fn (LineItem $lineItem) => \in_array($lineItem->getType(), $this->types, true)
        ));
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Checkout\Cart\LineItem\Group\Helpers\Traits;

use HeyFrame\Core\Checkout\Cart\Rule\LineItemListPriceRule;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemRule;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemUnitPriceRule;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemWithQuantityRule;
use HeyFrame\Core\Content\Rule\RuleEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Container\AndRule;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('checkout')]
trait RulesTestFixtureBehaviour
{
    /**
     * Build a rule entity with the provided rule
     * inside the payload property.
     */
    private function buildRuleEntity(Rule $rule): RuleEntity
    {
        $rules = new AndRule(
            [
                $rule,
            ]
        );

        $ruleEntity = new RuleEntity();
        $ruleEntity->setId(Uuid::randomHex());
        $ruleEntity->setPayload($rules);

        return $ruleEntity;
    }

    /**
     * Gets a minimum price rule with the provided price value.
     */
    private function getMinPriceRule(float $minPrice): LineItemUnitPriceRule
    {
        $rule = new LineItemUnitPriceRule();
        $rule->assign(['amount' => $minPrice, 'operator' => LineItemUnitPriceRule::OPERATOR_GTE]);

        return $rule;
    }

    /**
     * Gets a minimum quantity rule for the provided line item Id.
     */
    private function getMinQuantityRule(string $itemID, int $quantity): LineItemWithQuantityRule
    {
        $rule = new LineItemWithQuantityRule();
        $rule->assign(['id' => $itemID, 'quantity' => $quantity, 'operator' => LineItemWithQuantityRule::OPERATOR_GTE]);

        return $rule;
    }

    /**
     * @param array<mixed> $itemIDs
     */
    private function getProductsRule(array $itemIDs): LineItemRule
    {
        $rule = new LineItemRule();
        $rule->assign(['identifiers' => $itemIDs, 'operator' => LineItemRule::OPERATOR_EQ]);

        return $rule;
    }

    private function getLineItemListPriceRule(float $price): LineItemListPriceRule
    {
        $rule = new LineItemListPriceRule();
        $rule->assign(['amount' => $price, 'operator' => LineItemListPriceRule::OPERATOR_GTE]);

        return $rule;
    }
}

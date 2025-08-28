<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Rule;

use HeyFrame\Core\Checkout\CheckoutRuleScope;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Container\DaysSinceRule;
use HeyFrame\Core\Framework\Rule\RuleScope;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class DaysSinceLastOrderRule extends DaysSinceRule
{
    final public const RULE_NAME = 'customerDaysSinceLastOrder';

    public int $count;

    protected function getDate(RuleScope $scope): ?\DateTimeInterface
    {
        return $scope->getChannelContext()->getCustomer()?->getLastOrderDate();
    }

    protected function supportsScope(RuleScope $scope): bool
    {
        return $scope instanceof CheckoutRuleScope;
    }
}

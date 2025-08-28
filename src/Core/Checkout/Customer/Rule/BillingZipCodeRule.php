<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Rule;

use HeyFrame\Core\Checkout\CheckoutRuleScope;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Container\ZipCodeRule;
use HeyFrame\Core\Framework\Rule\RuleComparison;
use HeyFrame\Core\Framework\Rule\RuleScope;

#[Package('fundamentals@after-sales')]
class BillingZipCodeRule extends ZipCodeRule
{
    final public const RULE_NAME = 'customerBillingZipCode';

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CheckoutRuleScope) {
            return false;
        }

        if (!$customer = $scope->getChannelContext()->getCustomer()) {
            return RuleComparison::isNegativeOperator($this->operator);
        }

        if (!$address = $customer->getActiveBillingAddress()) {
            return RuleComparison::isNegativeOperator($this->operator);
        }

        return $this->matchZipCode($address);
    }
}

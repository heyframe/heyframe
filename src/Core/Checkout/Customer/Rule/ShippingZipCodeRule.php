<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Rule;

use HeyFrame\Core\Checkout\CheckoutRuleScope;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Container\ZipCodeRule;
use HeyFrame\Core\Framework\Rule\RuleComparison;
use HeyFrame\Core\Framework\Rule\RuleScope;

#[Package('fundamentals@after-sales')]
class ShippingZipCodeRule extends ZipCodeRule
{
    final public const RULE_NAME = 'customerShippingZipCode';

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CheckoutRuleScope) {
            return false;
        }

        if (!$address = $scope->getChannelContext()->getShippingLocation()->getAddress()) {
            return RuleComparison::isNegativeOperator($this->operator);
        }

        return $this->matchZipCode($address);
    }
}

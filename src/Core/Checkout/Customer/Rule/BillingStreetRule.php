<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Rule;

use HeyFrame\Core\Checkout\CheckoutRuleScope;
use HeyFrame\Core\Checkout\Customer\CustomerException;
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
class BillingStreetRule extends Rule
{
    final public const RULE_NAME = 'customerBillingStreet';

    /**
     * @internal
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        protected ?string $streetName = null
    ) {
        parent::__construct();
    }

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

        if (!\is_string($this->streetName) && $this->operator !== self::OPERATOR_EMPTY) {
            throw CustomerException::unsupportedValue(\gettype($this->streetName), self::class);
        }

        return RuleComparison::string($address->getStreet(), $this->streetName ?? '', $this->operator);
    }

    public function getConstraints(): array
    {
        $constraints = [
            'operator' => RuleConstraints::stringOperators(),
        ];

        if ($this->operator === self::OPERATOR_EMPTY) {
            return $constraints;
        }

        $constraints['streetName'] = RuleConstraints::string();

        return $constraints;
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_STRING, true)
            ->stringField('streetName');
    }
}

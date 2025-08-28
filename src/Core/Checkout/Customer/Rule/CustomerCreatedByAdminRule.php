<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Rule;

use HeyFrame\Core\Checkout\CheckoutRuleScope;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class CustomerCreatedByAdminRule extends Rule
{
    final public const RULE_NAME = 'customerCreatedByAdmin';

    /**
     * @internal
     */
    public function __construct(protected bool $shouldCustomerBeCreatedByAdmin = true)
    {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CheckoutRuleScope) {
            return false;
        }

        if (!$customer = $scope->getChannelContext()->getCustomer()) {
            return false;
        }

        return $this->shouldCustomerBeCreatedByAdmin === (bool) $customer->getCreatedById();
    }

    public function getConstraints(): array
    {
        return [
            'shouldCustomerBeCreatedByAdmin' => RuleConstraints::bool(true),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->booleanField('shouldCustomerBeCreatedByAdmin');
    }
}

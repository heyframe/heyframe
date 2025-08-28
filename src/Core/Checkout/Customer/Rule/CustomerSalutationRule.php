<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Rule;

use HeyFrame\Core\Checkout\CheckoutRuleScope;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\RuleComparison;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;
use HeyFrame\Core\System\Salutation\SalutationDefinition;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class CustomerSalutationRule extends Rule
{
    final public const RULE_NAME = 'customerSalutation';

    /**
     * @param list<string>|null $salutationIds
     *
     * @internal
     */
    public function __construct(
        public string $operator = self::OPERATOR_EQ,
        public ?array $salutationIds = null
    ) {
        parent::__construct();
    }

    public function getConstraints(): array
    {
        $constraints = [
            'operator' => RuleConstraints::uuidOperators(true),
        ];

        if ($this->operator === self::OPERATOR_EMPTY) {
            return $constraints;
        }

        $constraints['salutationIds'] = RuleConstraints::uuids();

        return $constraints;
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CheckoutRuleScope) {
            return false;
        }

        if (!$customer = $scope->getChannelContext()->getCustomer()) {
            return RuleComparison::isNegativeOperator($this->operator);
        }
        $salutation = $customer->getSalutation();

        if ($this->operator === self::OPERATOR_EMPTY) {
            return $salutation === null;
        }

        if ($salutation === null) {
            return RuleComparison::isNegativeOperator($this->operator);
        }

        return RuleComparison::uuids([$salutation->getId()], $this->salutationIds, $this->operator);
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_STRING, true, true)
            ->entitySelectField('salutationIds', SalutationDefinition::ENTITY_NAME, true, ['labelProperty' => 'displayName']);
    }
}

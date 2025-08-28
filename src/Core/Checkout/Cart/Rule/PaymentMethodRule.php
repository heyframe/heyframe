<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\Payment\PaymentMethodDefinition;
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
class PaymentMethodRule extends Rule
{
    final public const RULE_NAME = 'paymentMethod';

    /**
     * @param list<string> $paymentMethodIds
     *
     * @internal
     */
    public function __construct(
        protected string $operator = Rule::OPERATOR_EQ,
        protected array $paymentMethodIds = []
    ) {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        return RuleComparison::uuids([$scope->getChannelContext()->getPaymentMethod()->getId()], $this->paymentMethodIds, $this->operator);
    }

    public function getConstraints(): array
    {
        return [
            'paymentMethodIds' => RuleConstraints::uuids(),
            'operator' => RuleConstraints::uuidOperators(false),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_STRING, false, true)
            ->entitySelectField('paymentMethodIds', PaymentMethodDefinition::ENTITY_NAME, true);
    }
}

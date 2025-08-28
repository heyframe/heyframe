<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\Shipping\ShippingMethodDefinition;
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
class ShippingMethodRule extends Rule
{
    final public const RULE_NAME = 'shippingMethod';

    /**
     * @var list<string>
     */
    protected array $shippingMethodIds;

    protected string $operator;

    public function match(RuleScope $scope): bool
    {
        return RuleComparison::uuids([$scope->getChannelContext()->getShippingMethod()->getId()], $this->shippingMethodIds, $this->operator);
    }

    public function getConstraints(): array
    {
        return [
            'shippingMethodIds' => RuleConstraints::uuids(),
            'operator' => RuleConstraints::uuidOperators(false),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_STRING, false, true)
            ->entitySelectField('shippingMethodIds', ShippingMethodDefinition::ENTITY_NAME, true);
    }
}

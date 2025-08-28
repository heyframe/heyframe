<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Rule;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\FlowRule;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class OrderTrackingCodeRule extends FlowRule
{
    public const RULE_NAME = 'orderTrackingCode';

    /**
     * @internal
     */
    public function __construct(protected bool $isSet = false)
    {
        parent::__construct();
    }

    public function getConstraints(): array
    {
        return [
            'isSet' => RuleConstraints::bool(true),
        ];
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof FlowRuleScope) {
            return false;
        }

        if (!$deliveries = $scope->getOrder()->getDeliveries()) {
            return false;
        }

        $value = 0;
        foreach ($deliveries->getElements() as $delivery) {
            $value += \count(array_filter($delivery->getTrackingCodes()));
        }

        return $value > 0 === $this->isSet;
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->booleanField('isSet');
    }
}

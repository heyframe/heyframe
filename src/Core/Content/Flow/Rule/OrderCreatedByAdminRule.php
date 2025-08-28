<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Rule;

use HeyFrame\Core\Framework\Rule\FlowRule;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;

/**
 * @final
 */
class OrderCreatedByAdminRule extends FlowRule
{
    final public const RULE_NAME = 'orderCreatedByAdmin';

    /**
     * @internal
     */
    public function __construct(protected bool $shouldOrderBeCreatedByAdmin = true)
    {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof FlowRuleScope) {
            return false;
        }

        return $this->shouldOrderBeCreatedByAdmin === (bool) $scope->getOrder()->getCreatedById();
    }

    public function getConstraints(): array
    {
        return [
            'shouldOrderBeCreatedByAdmin' => RuleConstraints::bool(true),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())->booleanField('shouldOrderBeCreatedByAdmin');
    }
}

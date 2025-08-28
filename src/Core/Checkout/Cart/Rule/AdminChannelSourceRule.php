<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\CheckoutRuleScope;
use HeyFrame\Core\Framework\Api\Context\AdminChannelApiSource;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class AdminChannelSourceRule extends Rule
{
    final public const RULE_NAME = 'adminChannelSource';

    /**
     * @internal
     */
    public function __construct(protected bool $hasAdminChannelSource = false)
    {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CheckoutRuleScope) {
            return false;
        }

        $hasAdminChannelSource = $scope->getContext()->getSource() instanceof AdminChannelApiSource;

        if ($this->hasAdminChannelSource) {
            return $hasAdminChannelSource;
        }

        return !$hasAdminChannelSource;
    }

    public function getConstraints(): array
    {
        return [
            'hasAdminChannelSource' => RuleConstraints::bool(),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())->booleanField('hasAdminChannelSource');
    }
}

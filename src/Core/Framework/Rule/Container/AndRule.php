<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Rule\Container;

use HeyFrame\Core\Framework\Rule\RuleScope;

/**
 * AndRule returns true, if all child-rules are true
 */
class AndRule extends Container
{
    final public const RULE_NAME = 'andContainer';

    public function match(RuleScope $scope): bool
    {
        foreach ($this->rules as $rule) {
            if (!$rule->match($scope)) {
                return false;
            }
        }

        return true;
    }
}

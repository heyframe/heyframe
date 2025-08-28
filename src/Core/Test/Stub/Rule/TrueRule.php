<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Stub\Rule;

use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\RuleScope;

class TrueRule extends Rule
{
    final public const RULE_NAME = 'true';

    public function match(RuleScope $matchContext): bool
    {
        return true;
    }

    public function getConstraints(): array
    {
        return [];
    }
}

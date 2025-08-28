<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Content\Rule\RuleCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
abstract class AbstractRuleLoader
{
    abstract public function getDecorated(): AbstractRuleLoader;

    abstract public function load(Context $context): RuleCollection;
}

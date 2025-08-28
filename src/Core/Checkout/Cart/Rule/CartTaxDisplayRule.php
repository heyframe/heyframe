<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class CartTaxDisplayRule extends Rule
{
    final public const RULE_NAME = 'cartTaxDisplay';

    /**
     * @internal
     */
    public function __construct(protected string $taxDisplay = CartPrice::TAX_STATE_GROSS)
    {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        return $this->taxDisplay === $scope->getChannelContext()->getTaxState();
    }

    public function getConstraints(): array
    {
        return [
            'taxDisplay' => RuleConstraints::string(),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->selectField('taxDisplay', [CartPrice::TAX_STATE_GROSS, CartPrice::TAX_STATE_NET]);
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Price;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use HeyFrame\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class QuantityPriceCalculator
{
    /**
     * @internal
     */
    public function __construct(
        private readonly GrossPriceCalculator $grossPriceCalculator,
        private readonly NetPriceCalculator $netPriceCalculator
    ) {
    }

    public function calculate(QuantityPriceDefinition $definition, ChannelContext $context): CalculatedPrice
    {
        if ($context->getTaxState() === CartPrice::TAX_STATE_GROSS) {
            $price = $this->grossPriceCalculator->calculate($definition, $context->getItemRounding());
        } else {
            $price = $this->netPriceCalculator->calculate($definition, $context->getItemRounding());
        }

        if ($context->getTaxState() === CartPrice::TAX_STATE_FREE) {
            $price->assign([
                'taxRules' => new TaxRuleCollection(),
                'calculatedTaxes' => new CalculatedTaxCollection(),
            ]);
        }

        return $price;
    }
}

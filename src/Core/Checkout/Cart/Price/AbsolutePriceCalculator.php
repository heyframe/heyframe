<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Price;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\PriceCollection;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Checkout\Cart\Tax\PercentageTaxRuleBuilder;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class AbsolutePriceCalculator
{
    /**
     * @internal
     */
    public function __construct(
        private readonly QuantityPriceCalculator $priceCalculator,
        private readonly PercentageTaxRuleBuilder $percentageTaxRuleBuilder
    ) {
    }

    public function calculate(float $price, PriceCollection $prices, ChannelContext $context, int $quantity = 1): CalculatedPrice
    {
        $taxRules = $this->percentageTaxRuleBuilder->buildCollectionRules($prices->getCalculatedTaxes(), $prices->getTotalPriceAmount());

        $definition = new QuantityPriceDefinition($price, $taxRules, $quantity);

        return $this->priceCalculator->calculate($definition, $context);
    }
}

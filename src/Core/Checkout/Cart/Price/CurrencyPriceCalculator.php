<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Price;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\PriceCollection as CalculatedPriceCollection;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Checkout\Cart\Tax\PercentageTaxRuleBuilder;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class CurrencyPriceCalculator
{
    /**
     * @internal
     */
    public function __construct(
        private readonly QuantityPriceCalculator $priceCalculator,
        private readonly PercentageTaxRuleBuilder $percentageTaxRuleBuilder
    ) {
    }

    public function calculate(PriceCollection $price, CalculatedPriceCollection $prices, ChannelContext $context, int $quantity = 1): CalculatedPrice
    {
        $currency = $price->getCurrencyPrice($context->getCurrencyId());

        if (!$currency) {
            throw CartException::invalidPriceDefinition();
        }

        $value = $context->getTaxState() === CartPrice::TAX_STATE_GROSS ? $currency->getGross() : $currency->getNet();

        if ($currency->getCurrencyId() !== $context->getCurrencyId()) {
            $value *= $context->getCurrency()->getFactor();
        }

        $taxRules = $this->percentageTaxRuleBuilder->buildCollectionRules($prices->getCalculatedTaxes(), $prices->getTotalPriceAmount());
        $definition = new QuantityPriceDefinition($value, $taxRules, $quantity);

        return $this->priceCalculator->calculate($definition, $context);
    }
}

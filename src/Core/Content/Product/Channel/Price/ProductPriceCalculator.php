<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Price;

use HeyFrame\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\PriceCollection as CalculatedPriceCollection;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Checkout\Cart\Price\Struct\ReferencePriceDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductPrice\ProductPriceCollection;
use HeyFrame\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CalculatedCheapestPrice;
use HeyFrame\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CheapestPrice;
use HeyFrame\Core\Content\Product\Extension\ProductPriceCalculationExtension;
use HeyFrame\Core\Content\Product\ProductException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\Price;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use HeyFrame\Core\Framework\Extensions\ExtensionDispatcher;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('inventory')]
class ProductPriceCalculator extends AbstractProductPriceCalculator
{
    /*
     * @internal
     *
     */
    public function __construct(
        private readonly QuantityPriceCalculator $calculator,
        private readonly ExtensionDispatcher $extensions,
    ) {
    }

    public function getDecorated(): AbstractProductPriceCalculator
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @param iterable<Entity> $products
     */
    public function calculate(iterable $products, ChannelContext $context): void
    {
        // allows full service decoration
        $this->extensions->publish(
            name: ProductPriceCalculationExtension::NAME,
            extension: new ProductPriceCalculationExtension($products, $context),
            function: $this->_calculate(...)
        );
    }

    public function reset(): void
    {
        $this->units = null;
    }

    /**
     * @param iterable<Entity> $products
     */
    private function _calculate(iterable $products, ChannelContext $context): void
    {
        foreach ($products as $product) {
            $this->calculatePrice($product, $context);
            $this->calculateAdvancePrices($product, $context);
            $this->calculateCheapestPrice($product, $context);
        }
    }

    private function calculatePrice(Entity $product, ChannelContext $context): void
    {
        $price = $product->get('price');

        if ($price === null) {
            return;
        }
        $reference = ReferencePriceDto::createFromEntity($product);

        $definition = $this->buildDefinition($product, $price, $context, $reference);

        $price = $this->calculator->calculate($definition, $context);

        $product->assign([
            'calculatedPrice' => $price,
        ]);
    }

    private function calculateAdvancePrices(Entity $product, ChannelContext $context): void
    {
        $prices = $product->get('prices');

        $product->assign(['calculatedPrices' => new CalculatedPriceCollection()]);
        if ($prices === null) {
            return;
        }

        if (!$prices instanceof ProductPriceCollection) {
            return;
        }

        $prices = $this->filterRulePrices($prices, $context);
        if ($prices === null) {
            return;
        }
        $prices->sortByQuantity();

        $reference = ReferencePriceDto::createFromEntity($product);

        $calculated = new CalculatedPriceCollection();
        foreach ($prices as $price) {
            $quantity = $price->getQuantityEnd() ?? $price->getQuantityStart();

            $definition = $this->buildDefinition($product, $price->getPrice(), $context, $reference, $quantity);

            $calculated->add($this->calculator->calculate($definition, $context));
        }

        $product->assign(['calculatedPrices' => $calculated]);
    }

    private function calculateCheapestPrice(Entity $product, ChannelContext $context): void
    {
        $cheapest = $product->get('cheapestPrice');

        if (!$cheapest instanceof CheapestPrice) {
            $price = $product->get('price');
            if ($price === null) {
                return;
            }

            $reference = ReferencePriceDto::createFromEntity($product);

            $definition = $this->buildDefinition($product, $price, $context, $reference);

            $calculated = CalculatedCheapestPrice::createFrom(
                $this->calculator->calculate($definition, $context)
            );

            $prices = $product->get('calculatedPrices');

            $hasRange = $prices instanceof CalculatedPriceCollection && $prices->count() > 1;

            $calculated->setHasRange($hasRange);

            $product->assign(['calculatedCheapestPrice' => $calculated]);

            return;
        }

        $reference = ReferencePriceDto::createFromCheapestPrice($cheapest);

        $definition = $this->buildDefinition($product, $cheapest->getPrice(), $context, $reference);

        $calculated = CalculatedCheapestPrice::createFrom(
            $this->calculator->calculate($definition, $context)
        );
        $calculated->setVariantId($cheapest->getVariantId());

        $calculated->setHasRange($cheapest->hasRange());

        $product->assign(['calculatedCheapestPrice' => $calculated]);
    }

    private function buildDefinition(
        Entity $product,
        PriceCollection $prices,
        ChannelContext $context,
        ReferencePriceDto $reference,
        int $quantity = 1
    ): QuantityPriceDefinition {
        $price = $this->getPriceValue($prices, $context);

        $taxId = $product->get('taxId');
        $definition = new QuantityPriceDefinition($price, $context->buildTaxRules($taxId), $quantity);
        $definition->setReferencePriceDefinition(
            $this->buildReferencePriceDefinition($reference)
        );
        $definition->setListPrice(
            $this->getListPrice($prices, $context)
        );
        $definition->setRegulationPrice(
            $this->getRegulationPrice($prices, $context)
        );

        return $definition;
    }

    private function getPriceValue(PriceCollection $price, ChannelContext $context): float
    {
        $currency = $price->getCurrencyPrice($context->getCurrencyId());
        if ($currency === null) {
            throw ProductException::noPriceForCurrency($context->getCurrency());
        }

        $value = $this->getPriceForTaxState($currency, $context);

        if ($currency->getCurrencyId() !== $context->getCurrencyId()) {
            $value *= $context->getContext()->getCurrencyFactor();
        }

        return $value;
    }

    private function getPriceForTaxState(Price $price, ChannelContext $context): float
    {
        if ($context->getTaxState() === CartPrice::TAX_STATE_GROSS) {
            return $price->getGross();
        }

        return $price->getNet();
    }

    private function getListPrice(PriceCollection $prices, ChannelContext $context): ?float
    {
        $price = $prices->getCurrencyPrice($context->getCurrencyId());
        if ($price === null || $price->getListPrice() === null) {
            return null;
        }

        $value = $this->getPriceForTaxState($price->getListPrice(), $context);

        if ($price->getCurrencyId() !== $context->getCurrencyId()) {
            $value *= $context->getContext()->getCurrencyFactor();
        }

        return $value;
    }

    private function getRegulationPrice(PriceCollection $prices, ChannelContext $context): ?float
    {
        $price = $prices->getCurrencyPrice($context->getCurrencyId());
        if ($price === null || $price->getRegulationPrice() === null) {
            return null;
        }

        $taxPrice = $this->getPriceForTaxState($price, $context);
        $value = $this->getPriceForTaxState($price->getRegulationPrice(), $context);

        if ($taxPrice === 0.0) {
            return null;
        }

        if ($price->getCurrencyId() !== $context->getCurrencyId()) {
            $value *= $context->getContext()->getCurrencyFactor();
        }

        return $value;
    }

    private function buildReferencePriceDefinition(ReferencePriceDto $definition): ?ReferencePriceDefinition
    {
        if (
            $definition->getPurchase() === null
            || $definition->getPurchase() <= 0
            || $definition->getUnitId() === null
            || $definition->getReference() === null
            || $definition->getReference() <= 0
            || $definition->getPurchase() === $definition->getReference()
        ) {
            return null;
        }

        return new ReferencePriceDefinition(
            $definition->getPurchase(),
            $definition->getReference(),
        );
    }

    private function filterRulePrices(ProductPriceCollection $rules, ChannelContext $context): ?ProductPriceCollection
    {
        foreach ($context->getRuleIds() as $ruleId) {
            $filtered = $rules->filterByRuleId($ruleId);

            if (\count($filtered) > 0) {
                return $filtered;
            }
        }

        return null;
    }
}

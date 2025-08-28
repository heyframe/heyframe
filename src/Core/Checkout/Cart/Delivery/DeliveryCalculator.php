<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Delivery;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartBehavior;
use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\Delivery\Struct\Delivery;
use HeyFrame\Core\Checkout\Cart\Delivery\Struct\DeliveryCollection;
use HeyFrame\Core\Checkout\Cart\LineItem\CartDataCollection;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Checkout\Cart\Tax\PercentageTaxRuleBuilder;
use HeyFrame\Core\Checkout\CheckoutPermissions;
use HeyFrame\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice\ShippingMethodPriceCollection;
use HeyFrame\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice\ShippingMethodPriceEntity;
use HeyFrame\Core\Checkout\Shipping\Cart\Error\ShippingMethodBlockedError;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodEntity;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\Price;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\FloatComparator;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class DeliveryCalculator
{
    final public const CALCULATION_BY_LINE_ITEM_COUNT = 1;

    final public const CALCULATION_BY_PRICE = 2;

    final public const CALCULATION_BY_WEIGHT = 3;

    final public const CALCULATION_BY_VOLUME = 4;

    /**
     * @internal
     */
    public function __construct(
        private readonly QuantityPriceCalculator $priceCalculator,
        private readonly PercentageTaxRuleBuilder $percentageTaxRuleBuilder
    ) {
    }

    public function calculate(CartDataCollection $data, Cart $cart, DeliveryCollection $deliveries, ChannelContext $context): void
    {
        foreach ($deliveries as $delivery) {
            $this->calculateDelivery($data, $cart, $delivery, $context);
        }
    }

    private function calculateDelivery(CartDataCollection $data, Cart $cart, Delivery $delivery, ChannelContext $context): void
    {
        $costs = null;
        $manualShippingCost = $cart->getExtension(DeliveryProcessor::MANUAL_SHIPPING_COSTS);
        $manualShippingCost = $manualShippingCost instanceof CalculatedPrice ? $manualShippingCost : null;
        if ($delivery->getShippingCosts()->getUnitPrice() > 0 || $manualShippingCost) {
            $costs = $this->calculateShippingCosts(
                $delivery->getShippingMethod(),
                new PriceCollection([
                    new Price(
                        Defaults::CURRENCY,
                        $delivery->getShippingCosts()->getTotalPrice(),
                        $delivery->getShippingCosts()->getTotalPrice(),
                        false
                    ),
                ]),
                $delivery->getPositions()->getLineItems(),
                $context,
                $manualShippingCost
            );

            $delivery->setShippingCosts($costs);

            return;
        }

        if (
            $this->hasDeliveryPriceRecalculationSkipWithZeroUnitPrice($cart->getBehavior(), $delivery->getShippingCosts()->getUnitPrice())
            || $this->hasDeliveryWithOnlyShippingFreeItems($delivery)
        ) {
            $costs = $this->calculateShippingCosts(
                $delivery->getShippingMethod(),
                new PriceCollection([new Price(Defaults::CURRENCY, 0, 0, false)]),
                $delivery->getPositions()->getLineItems(),
                $context
            );
            $delivery->setShippingCosts($costs);

            return;
        }

        $key = DeliveryProcessor::buildKey($delivery->getShippingMethod()->getId());

        if (!$data->has($key)) {
            throw CartException::shippingMethodNotFound($delivery->getShippingMethod()->getId());
        }

        /** @var ShippingMethodEntity $shippingMethod */
        $shippingMethod = $data->get($key);

        foreach ($context->getRuleIds() as $ruleId) {
            /** @var ShippingMethodPriceCollection $shippingPrices */
            $shippingPrices = $shippingMethod->getPrices()->filterByProperty('ruleId', $ruleId);

            $costs = $this->getMatchingPriceOfRule($delivery, $context, $shippingPrices);
            if ($costs !== null) {
                break;
            }
        }

        // Fetch default price if no rule matched
        if ($costs === null) {
            /** @var ShippingMethodPriceCollection $shippingPrices */
            $shippingPrices = $shippingMethod->getPrices()->filterByProperty('ruleId', null);
            $costs = $this->getMatchingPriceOfRule($delivery, $context, $shippingPrices);
        }

        if (!$costs) {
            $cart->addErrors(
                new ShippingMethodBlockedError((string) $shippingMethod->getTranslation('name'))
            );

            return;
        }

        $delivery->setShippingCosts($costs);
    }

    private function hasDeliveryWithOnlyShippingFreeItems(Delivery $delivery): bool
    {
        foreach ($delivery->getPositions()->getLineItems()->getIterator() as $lineItem) {
            if ($lineItem->getDeliveryInformation() && !$lineItem->getDeliveryInformation()->getFreeDelivery()) {
                return false;
            }
        }

        return true;
    }

    private function matches(Delivery $delivery, ShippingMethodPriceEntity $shippingMethodPrice, ChannelContext $context): bool
    {
        if ($shippingMethodPrice->getCalculationRuleId()) {
            return \in_array($shippingMethodPrice->getCalculationRuleId(), $context->getRuleIds(), true);
        }

        $start = $shippingMethodPrice->getQuantityStart();
        $end = $shippingMethodPrice->getQuantityEnd();

        $value = match ($shippingMethodPrice->getCalculation()) {
            self::CALCULATION_BY_PRICE => $delivery->getPositions()->getWithoutDeliveryFree()->getPrices()->getTotalPriceAmount(),
            self::CALCULATION_BY_LINE_ITEM_COUNT => $delivery->getPositions()->getWithoutDeliveryFree()->getQuantity(),
            self::CALCULATION_BY_WEIGHT => $delivery->getPositions()->getWithoutDeliveryFree()->getWeight(),
            self::CALCULATION_BY_VOLUME => $delivery->getPositions()->getWithoutDeliveryFree()->getVolume(),
            default => $delivery->getPositions()->getWithoutDeliveryFree()->getLineItems()->getPrices()->getTotalPriceAmount() / 100,
        };

        // $end (optional) exclusive
        return (!$start || FloatComparator::greaterThanOrEquals($value, $start)) && (!$end || FloatComparator::lessThanOrEquals($value, $end));
    }

    private function calculateShippingCosts(ShippingMethodEntity $shippingMethod, PriceCollection $priceCollection, LineItemCollection $calculatedLineItems, ChannelContext $context, ?CalculatedPrice $manualShippingCost = null): CalculatedPrice
    {
        switch ($shippingMethod->getTaxType()) {
            case ShippingMethodEntity::TAX_TYPE_HIGHEST:
                $rules = $calculatedLineItems->getPrices()->getHighestTaxRule();

                break;

            case ShippingMethodEntity::TAX_TYPE_FIXED:
                $taxId = $shippingMethod->getTaxId();

                if ($taxId !== null) {
                    $rules = $context->buildTaxRules($taxId);

                    break;
                }

                // no break
            default:
                $rules = $this->percentageTaxRuleBuilder->buildCollectionRules(
                    $calculatedLineItems->getPrices()->getCalculatedTaxes(),
                    $calculatedLineItems->getPrices()->getTotalPriceAmount(),
                );
        }

        if ($manualShippingCost !== null) {
            $price = $manualShippingCost->getTotalPrice();
        } else {
            $price = $this->getCurrencyPrice($priceCollection, $context);
        }

        $definition = new QuantityPriceDefinition($price, $rules, 1);

        return $this->priceCalculator->calculate($definition, $context);
    }

    private function getCurrencyPrice(PriceCollection $priceCollection, ChannelContext $context): float
    {
        /** @var Price $price */
        $price = $priceCollection->getCurrencyPrice($context->getCurrencyId());

        $value = $this->getPriceForTaxState($price, $context);

        if ($price->getCurrencyId() === Defaults::CURRENCY) {
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

    private function getMatchingPriceOfRule(Delivery $delivery, ChannelContext $context, ShippingMethodPriceCollection $shippingPrices): ?CalculatedPrice
    {
        $shippingPrices->sort(
            function (ShippingMethodPriceEntity $priceEntityA, ShippingMethodPriceEntity $priceEntityB) use ($context) {
                $priceCollectionA = $priceEntityA->getCurrencyPrice();
                $priceA = $priceCollectionA ? $this->getCurrencyPrice($priceCollectionA, $context) : null;

                $priceCollectionB = $priceEntityB->getCurrencyPrice();
                $priceB = $priceCollectionB ? $this->getCurrencyPrice($priceCollectionB, $context) : null;

                return $priceA <=> $priceB;
            }
        );

        $costs = null;
        foreach ($shippingPrices as $shippingPrice) {
            if (!$this->matches($delivery, $shippingPrice, $context)) {
                continue;
            }
            $price = $shippingPrice->getCurrencyPrice();
            if (!$price) {
                continue;
            }
            $costs = $this->calculateShippingCosts(
                $delivery->getShippingMethod(),
                $price,
                $delivery->getPositions()->getLineItems(),
                $context
            );

            break;
        }

        return $costs;
    }

    private function hasDeliveryPriceRecalculationSkipWithZeroUnitPrice(?CartBehavior $behavior, float $unitPrice): bool
    {
        return $behavior
            && $behavior->hasPermission(CheckoutPermissions::SKIP_DELIVERY_PRICE_RECALCULATION)
            && $unitPrice === 0.0;
    }
}

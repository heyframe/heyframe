<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\LineItem;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use HeyFrame\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class LineItemQuantitySplitter
{
    /**
     * @internal
     */
    public function __construct(private readonly QuantityPriceCalculator $quantityPriceCalculator)
    {
    }

    /**
     * Gets a new line item with only the provided quantity amount
     * along a ready-to-use calculated price.
     *
     * @throws CartException
     */
    public function split(LineItem $item, int $quantity, ChannelContext $context): LineItem
    {
        if ($item->getQuantity() === $quantity) {
            return clone $item;
        }

        // clone the original line item
        $tmpItem = clone $item;

        // use calculated item price
        /** @var CalculatedPrice $lineItemPrice */
        $lineItemPrice = $tmpItem->getPrice();

        $unitPrice = $lineItemPrice->getUnitPrice();

        $taxRules = $lineItemPrice->getTaxRules();

        // change the quantity to 1 single item
        $tmpItem->setQuantity($quantity);

        $definition = new QuantityPriceDefinition($unitPrice, $taxRules, $tmpItem->getQuantity());

        if (Feature::isActive('v6.8.0.0')) {
            $taxes = new CalculatedTaxCollection();
            foreach ($lineItemPrice->getCalculatedTaxes() as $tax) {
                $taxes->add(new CalculatedTax($tax->getTax() / $item->getQuantity() * $quantity, $tax->getTaxRate(), $tax->getPrice() / $item->getQuantity() * $quantity, $tax->getLabel()));
            }

            $price = new CalculatedPrice(
                $unitPrice,
                $unitPrice * $quantity,
                $taxes,
                $taxRules,
                $tmpItem->getQuantity(),
                $lineItemPrice->getReferencePrice(),
                $lineItemPrice->getListPrice(),
                $lineItemPrice->getRegulationPrice(),
            );
        } else {
            $price = $this->quantityPriceCalculator->calculate($definition, $context);

            $price->assign([
                'listPrice' => $lineItemPrice->getListPrice() ?? null,
            ]);
        }

        $tmpItem->setPrice($price);

        return $tmpItem;
    }
}

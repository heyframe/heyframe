<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\LineItem;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class LineItemQuantitySplitter
{
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

        // change the quantity to 1 single item
        $tmpItem->setQuantity($quantity);

        $price = new CalculatedPrice(
            $unitPrice,
            $unitPrice * $quantity,
            $tmpItem->getQuantity(),
            $lineItemPrice->getListPrice(),
            $lineItemPrice->getRegulationPrice(),
        );

        $tmpItem->setPrice($price);

        return $tmpItem;
    }
}

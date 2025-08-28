<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cart;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartValidatorInterface;
use HeyFrame\Core\Checkout\Cart\Error\ErrorCollection;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\CheckoutPermissions;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class ProductLineItemValidator implements CartValidatorInterface
{
    public function validate(Cart $cart, ErrorCollection $errors, ChannelContext $context): void
    {
        $behavior = $cart->getBehavior();
        if ($behavior !== null && $behavior->hasPermission(CheckoutPermissions::SKIP_PRODUCT_STOCK_VALIDATION)) {
            return;
        }

        $productLineItems = array_filter($cart->getLineItems()->getFlat(), static fn (LineItem $lineItem) => $lineItem->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE);

        $quantities = [];
        $refs = [];
        foreach ($productLineItems as $lineItem) {
            $productId = $lineItem->getReferencedId();
            if ($productId === null) {
                continue;
            }

            $quantities[$productId] = $lineItem->getQuantity() + ($quantities[$productId] ?? 0);

            // only needed one time to check max quantity
            $refs[$productId] = $lineItem;
        }

        foreach ($quantities as $productId => $quantity) {
            $lineItem = $refs[$productId];
            $quantityInformation = $lineItem->getQuantityInformation();
            if ($quantityInformation === null) {
                continue;
            }

            $minPurchase = $quantityInformation->getMinPurchase();
            $available = $quantityInformation->getMaxPurchase() ?? 0;
            $steps = $quantityInformation->getPurchaseSteps() ?? 1;

            if ($available >= $quantity) {
                continue;
            }

            $maxAvailable = (int) (floor(($available - $minPurchase) / $steps) * $steps + $minPurchase);

            $cart->addErrors(
                new ProductStockReachedError($productId, (string) $lineItem->getLabel(), $maxAvailable, false),
            );
        }
    }
}

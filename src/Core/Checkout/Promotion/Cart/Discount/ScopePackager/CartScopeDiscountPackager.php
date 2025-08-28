<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Cart\Discount\ScopePackager;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemQuantity;
use HeyFrame\Core\Checkout\Cart\LineItem\Group\LineItemQuantityCollection;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Checkout\Cart\Price\Struct\FilterableInterface;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemScope;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountLineItem;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountPackage;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountPackager;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class CartScopeDiscountPackager extends DiscountPackager
{
    public function getDecorated(): DiscountPackager
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * Gets all product line items of the entire cart that
     * match the rules and conditions of the provided discount item.
     */
    public function getMatchingItems(DiscountLineItem $discount, Cart $cart, ChannelContext $context): DiscountPackageCollection
    {
        $allItems = $cart->getLineItems()->filter(fn (LineItem $lineItem) => $lineItem->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE && $lineItem->isStackable());

        $priceDefinition = $discount->getPriceDefinition();
        if ($priceDefinition instanceof FilterableInterface && $priceDefinition->getFilter()) {
            $allItems = $allItems->filter(fn (LineItem $lineItem) => $priceDefinition->getFilter()->match(new LineItemScope($lineItem, $context)));
        }

        $discountPackage = $this->getDiscountPackage($allItems, $discount->isProductRestricted());
        if ($discountPackage === null) {
            return new DiscountPackageCollection([]);
        }

        return new DiscountPackageCollection([$discountPackage]);
    }

    private function getDiscountPackage(LineItemCollection $cartItems, bool $isAdvanceRuled): ?DiscountPackage
    {
        if (!Feature::isActive('PERFORMANCE_TWEAKS')) {
            $isAdvanceRuled = true;
        }

        $discountItems = [];

        foreach ($cartItems as $cartLineItem) {
            $item = new LineItemQuantity(
                $cartLineItem->getId(),
                $isAdvanceRuled ? 1 : $cartLineItem->getQuantity()
            );

            if ($isAdvanceRuled) {
                for ($i = 1; $i <= $cartLineItem->getQuantity(); ++$i) {
                    $discountItems[] = clone $item;
                }
            } else {
                $discountItems[] = $item;
            }
        }

        if (\count($discountItems) === 0) {
            return null;
        }

        // assign instead of add for performance reasons
        $collection = new LineItemQuantityCollection();
        $collection->assign(['elements' => $discountItems]);

        return new DiscountPackage($collection);
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\LineItem\Group;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;

#[Package('checkout')]
class ProductLineItemProvider extends AbstractProductLineItemProvider
{
    public function getDecorated(): AbstractProductLineItemProvider
    {
        throw new DecorationPatternException(self::class);
    }

    public function getProducts(Cart $cart): LineItemCollection
    {
        return $cart->getLineItems()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE);
    }
}

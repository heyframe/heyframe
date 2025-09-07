<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Checkout\Cart\Channel\Helper;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\ListPrice;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('fundamentals@after-sales')]
trait CartRuleHelperTrait
{
    protected static function createLineItem(
        string $type = LineItem::PRODUCT_LINE_ITEM_TYPE,
        int $quantity = 1,
        ?string $referenceId = null
    ): LineItem {
        return new LineItem(Uuid::randomHex(), $type, $referenceId, $quantity);
    }

    protected static function createContainerLineItem(LineItemCollection $childLineItemCollection): LineItem
    {
        return self::createLineItem('container-type')->setChildren($childLineItemCollection);
    }

    protected static function createLineItemWithPrice(string $type, float $price, ?ListPrice $listPrice = null): LineItem
    {
        return self::createLineItem($type)->setPrice(
            new CalculatedPrice(
                $price,
                $price,
                1,
                null,
                $listPrice
            )
        );
    }

    protected static function createCart(LineItemCollection $lineItemCollection): Cart
    {
        $cart = new Cart(Uuid::randomHex());
        $cart->addLineItems($lineItemCollection);

        return $cart;
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Checkout\Cart\LineItem\Group;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\LineItem\Group\AbstractProductLineItemProvider;
use HeyFrame\Core\Checkout\Cart\LineItem\Group\ProductLineItemProvider;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Tests\Unit\Core\Checkout\Cart\LineItem\Group\Helpers\Traits\LineItemTestFixtureBehaviour;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
#[CoversClass(ProductLineItemProvider::class)]
class ProductLineItemProviderTest extends TestCase
{
    use LineItemTestFixtureBehaviour;

    private AbstractProductLineItemProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new ProductLineItemProvider();
    }

    public function testIsMatchingReturnProductLineItem(): void
    {
        $cart = $this->getCart();

        static::assertCount(4, $cart->getLineItems());

        $lineItems = $this->provider->getProducts($cart);

        static::assertCount(1, $lineItems);
        static::assertNotNull($lineItems->first());
        static::assertSame(LineItem::PRODUCT_LINE_ITEM_TYPE, $lineItems->first()->getType());
    }

    public function testItThrowsDecorationPatternException(): void
    {
        $this->expectException(DecorationPatternException::class);

        $this->provider->getDecorated();
    }

    private function getCart(): Cart
    {
        $items = [
            new LineItem(Uuid::randomHex(), LineItem::PRODUCT_LINE_ITEM_TYPE),
            new LineItem(Uuid::randomHex(), LineItem::PROMOTION_LINE_ITEM_TYPE),
            new LineItem(Uuid::randomHex(), LineItem::CREDIT_LINE_ITEM_TYPE),
            new LineItem(Uuid::randomHex(), LineItem::CUSTOM_LINE_ITEM_TYPE),
        ];

        $cart = new Cart('token');
        $cart->addLineItems(new LineItemCollection($items));

        return $cart;
    }
}

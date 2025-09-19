<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Checkout\Cart\LineItem\Group\Helpers\Traits;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\ListPrice;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('checkout')]
trait LineItemTestFixtureBehaviour
{
    /**
     * Create a simple product line item with the provided price.
     */
    private function createProductItem(float $netPrice, float $taxRate, ?float $listPriceNet = null): LineItem
    {
        $product = new LineItem(Uuid::randomHex(), LineItem::PRODUCT_LINE_ITEM_TYPE);

        // allow quantity change
        $product->setStackable(true);

        $taxValue = $netPrice * ($taxRate / 100.0);

        $grossPrice = $netPrice + $taxValue;

        $listPrice = null;
        if ($listPriceNet !== null) {
            $listPrice = ListPrice::createFromUnitPrice($netPrice, $listPriceNet);
        }

        $product->setPrice(new CalculatedPrice(
            $grossPrice,
            $grossPrice,
            1,
            $listPrice,
            null,
        ));

        return $product;
    }
}

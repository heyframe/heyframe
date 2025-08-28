<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Integration\Traits\Promotion;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use HeyFrame\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use HeyFrame\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('checkout')]
trait PromotionLineItemTestFixtureBehaviour
{
    /**
     * Create a simple product line item with the provided price.
     */
    private function createProductItem(float $price, float $taxRate): LineItem
    {
        $product = new LineItem(Uuid::randomHex(), LineItem::PRODUCT_LINE_ITEM_TYPE);

        // allow quantity change
        $product->setStackable(true);

        $taxValue = $price * ($taxRate / 100.0);

        $calculatedTaxes = new CalculatedTaxCollection();
        $calculatedTaxes->add(new CalculatedTax($taxValue, $taxRate, $taxValue));

        $product->setPrice(new CalculatedPrice($price, $price, $calculatedTaxes, new TaxRuleCollection()));

        return $product;
    }
}

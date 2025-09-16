<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Stub\Checkout;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\ListPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\RegulationPrice;

class EmptyPrice extends CalculatedPrice
{
    public function __construct(
        float $unitPrice = 0,
        float $totalPrice = 0,
        int $quantity = 1,
        ?ListPrice $listPrice = null,
        ?RegulationPrice $regulationPrice = null
    ) {

        parent::__construct($unitPrice, $totalPrice, $quantity, $listPrice, $regulationPrice);
    }
}

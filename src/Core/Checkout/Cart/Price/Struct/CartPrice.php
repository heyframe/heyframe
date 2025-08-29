<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Price\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;
use HeyFrame\Core\Framework\Util\FloatComparator;

#[Package('checkout')]
class CartPrice extends Struct
{
    protected float $rawTotal;

    public function __construct(
        protected float $totalPrice,
        protected float $positionPrice,
        ?float $rawTotal = null
    ) {
        $this->totalPrice = FloatComparator::cast($totalPrice);
        $this->positionPrice = FloatComparator::cast($positionPrice);
        $rawTotal ??= $totalPrice;
        $this->rawTotal = FloatComparator::cast($rawTotal);
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function getPositionPrice(): float
    {
        return $this->positionPrice;
    }

    public static function createEmpty(): CartPrice
    {
        return new self(0, 0, 0);
    }

    public function getApiAlias(): string
    {
        return 'cart_price';
    }

    public function getRawTotal(): float
    {
        return $this->rawTotal;
    }
}

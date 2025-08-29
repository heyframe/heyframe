<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Price\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\FloatComparator;

#[Package('checkout')]
class ReferencePrice extends ReferencePriceDefinition
{
    public function __construct(
        protected float $price,
        float $purchaseUnit,
        float $referenceUnit,
    ) {
        parent::__construct($purchaseUnit, $referenceUnit);

        $this->price = FloatComparator::cast($price);
    }

    public function getPrice(): float
    {
        return FloatComparator::cast($this->price);
    }

    public function getApiAlias(): string
    {
        return 'cart_price_reference';
    }
}

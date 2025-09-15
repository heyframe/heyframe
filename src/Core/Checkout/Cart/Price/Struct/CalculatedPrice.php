<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Price\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;
use HeyFrame\Core\Framework\Util\FloatComparator;

#[Package('checkout')]
class CalculatedPrice extends Struct
{
    public function __construct(
        protected float $unitPrice,
        protected float $totalPrice,
        protected int $quantity = 1,
        protected ?ListPrice $listPrice = null,
        protected ?RegulationPrice $regulationPrice = null
    ) {
        $this->unitPrice = FloatComparator::cast($unitPrice);
        $this->totalPrice = FloatComparator::cast($totalPrice);
    }

    public function getTotalPrice(): float
    {
        return FloatComparator::cast($this->totalPrice);
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getListPrice(): ?ListPrice
    {
        return $this->listPrice;
    }

    public function getRegulationPrice(): ?RegulationPrice
    {
        return $this->regulationPrice;
    }

    public function getApiAlias(): string
    {
        return 'calculated_price';
    }

    /**
     * Changing a price should always be a full change, otherwise you have
     * mismatching information regarding the unit, total and tax values.
     */
    public function overwrite(float $unitPrice, float $totalPrice): void
    {
        $this->unitPrice = $unitPrice;
        $this->totalPrice = $totalPrice;
    }
}

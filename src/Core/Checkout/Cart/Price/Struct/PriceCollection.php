<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Price\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;
use HeyFrame\Core\Framework\Util\FloatComparator;

/**
 * @extends Collection<CalculatedPrice>
 */
#[Package('checkout')]
class PriceCollection extends Collection
{
    public function get($key): ?CalculatedPrice
    {
        $key = (int) $key;

        if ($this->has($key)) {
            return $this->elements[$key];
        }

        return null;
    }

    public function sum(): CalculatedPrice
    {
        return new CalculatedPrice(
            $this->getUnitPriceAmount(),
            $this->getTotalPriceAmount(),
        );
    }

    public function merge(self $prices): self
    {
        return new self(array_merge($this->elements, $prices->getElements()));
    }

    public function getApiAlias(): string
    {
        return 'cart_price_collection';
    }

    public function getUnitPriceAmount(): float
    {
        $prices = $this->map(fn (CalculatedPrice $price) => $price->getUnitPrice());

        return FloatComparator::cast(array_sum($prices));
    }

    public function getTotalPriceAmount(): float
    {
        $prices = $this->map(fn (CalculatedPrice $price) => $price->getTotalPrice());

        return FloatComparator::cast(array_sum($prices));
    }

    protected function getExpectedClass(): ?string
    {
        return CalculatedPrice::class;
    }
}

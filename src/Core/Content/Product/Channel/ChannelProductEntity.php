<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\PriceCollection;
use HeyFrame\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CalculatedCheapestPrice;
use HeyFrame\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CheapestPrice;
use HeyFrame\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CheapestPriceContainer;
use HeyFrame\Core\Content\Product\ProductEntity;
use HeyFrame\Core\Content\Property\PropertyGroupCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ChannelProductEntity extends ProductEntity
{
    protected PriceCollection $calculatedPrices;

    protected CalculatedPrice $calculatedPrice;

    protected ?PropertyGroupCollection $sortedProperties = null;

    protected CalculatedCheapestPrice $calculatedCheapestPrice;

    protected int $calculatedMaxPurchase;

    /**
     * The container will be resolved on product.loaded event and
     * the detected cheapest price will be set for the current context rules
     */
    protected CheapestPrice|CheapestPriceContainer|null $cheapestPrice = null;

    protected ?CheapestPriceContainer $cheapestPriceContainer = null;

    public function setCalculatedPrices(PriceCollection $prices): void
    {
        $this->calculatedPrices = $prices;
    }

    public function getCalculatedPrices(): PriceCollection
    {
        return $this->calculatedPrices;
    }

    public function getCalculatedPrice(): CalculatedPrice
    {
        return $this->calculatedPrice;
    }

    public function setCalculatedPrice(CalculatedPrice $calculatedPrice): void
    {
        $this->calculatedPrice = $calculatedPrice;
    }

    public function getSortedProperties(): ?PropertyGroupCollection
    {
        return $this->sortedProperties;
    }

    public function setSortedProperties(?PropertyGroupCollection $sortedProperties): void
    {
        $this->sortedProperties = $sortedProperties;
    }

    public function getCalculatedMaxPurchase(): int
    {
        return $this->calculatedMaxPurchase;
    }

    public function setCalculatedMaxPurchase(int $calculatedMaxPurchase): void
    {
        $this->calculatedMaxPurchase = $calculatedMaxPurchase;
    }

    public function getCalculatedCheapestPrice(): CalculatedCheapestPrice
    {
        return $this->calculatedCheapestPrice;
    }

    public function setCalculatedCheapestPrice(CalculatedCheapestPrice $calculatedCheapestPrice): void
    {
        $this->calculatedCheapestPrice = $calculatedCheapestPrice;
    }

    public function getCheapestPrice(): CheapestPrice|CheapestPriceContainer|null
    {
        return $this->cheapestPrice;
    }

    public function setCheapestPrice(?CheapestPrice $cheapestPrice): void
    {
        $this->cheapestPrice = $cheapestPrice;
    }

    public function setCheapestPriceContainer(CheapestPriceContainer $container): void
    {
        $this->cheapestPriceContainer = $container;
    }

    public function getCheapestPriceContainer(): ?CheapestPriceContainer
    {
        return $this->cheapestPriceContainer;
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Price;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\PriceCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class AmountCalculator
{
    /**
     * @internal
     */
    public function __construct(
        private readonly CashRounding $rounding,
    ) {
    }

    public function calculate(PriceCollection $prices, ChannelContext $context): CartPrice
    {
        return $this->calculateGrossAmount($prices, $context);
    }

    /**
     * Calculates the amount for a gross delivery.
     * `CalculatedPrice::netPrice` contains the summed gross prices minus amount of calculated taxes.
     * `CalculatedPrice::price` contains the summed gross prices
     * Calculated taxes are based on the gross prices
     */
    private function calculateGrossAmount(PriceCollection $prices, ChannelContext $context): CartPrice
    {
        $all = $prices;
        $totalPrice = $all->getTotalPriceAmount();

        $price = $this->rounding->cashRound(
            $totalPrice,
            $context->getTotalRounding()
        );

        return new CartPrice(
            $price,
            $prices->getTotalPriceAmount(),
            $totalPrice
        );
    }
}

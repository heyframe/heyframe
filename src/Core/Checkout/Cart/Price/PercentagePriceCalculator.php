<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Price;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\PriceCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class PercentagePriceCalculator
{
    /**
     * @internal
     */
    public function __construct(
        private readonly CashRounding $rounding,
    ) {
    }

    /**
     * Provide a negative percentage value for discount or a positive percentage value for a surcharge
     *
     * @param float $percentage 10.00 for 10%, -10.0 for -10%
     */
    public function calculate(float $percentage, PriceCollection $prices, ChannelContext $context): CalculatedPrice
    {
        $totalPrice = $prices->getTotalPriceAmount();
        $discount = $this->round(
            $totalPrice / 100 * $percentage,
            $context
        );

        return new CalculatedPrice(
            $discount,
            $discount
        );
    }

    private function round(float $price, ChannelContext $context): float
    {
        return $this->rounding->cashRound($price, $context->getItemRounding());
    }
}

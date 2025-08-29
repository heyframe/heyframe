<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Price;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\PriceCollection;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class AbsolutePriceCalculator
{
    /**
     * @internal
     */
    public function __construct(
        private readonly QuantityPriceCalculator $priceCalculator,
    ) {
    }

    public function calculate(float $price, PriceCollection $prices, ChannelContext $context, int $quantity = 1): CalculatedPrice
    {
        $definition = new QuantityPriceDefinition($price, $quantity);

        return $this->priceCalculator->calculate($definition, $context);
    }
}

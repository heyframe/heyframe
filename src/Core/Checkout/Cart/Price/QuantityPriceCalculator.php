<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Price;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class QuantityPriceCalculator
{
    /**
     * @internal
     */
    public function __construct(
        private readonly GrossPriceCalculator $grossPriceCalculator,
    ) {
    }

    public function calculate(QuantityPriceDefinition $definition, ChannelContext $context): CalculatedPrice
    {
        return $this->grossPriceCalculator->calculate($definition, $context->getItemRounding());
    }
}

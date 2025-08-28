<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use HeyFrame\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use HeyFrame\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class PriceDefinitionFactory
{
    /**
     * @param array<string, mixed> $priceDefinition
     */
    public function factory(Context $context, array $priceDefinition, string $lineItemType): PriceDefinitionInterface
    {
        if (!isset($priceDefinition['type'])) {
            throw CartException::invalidPriceFieldTypeException('none');
        }

        return match ($priceDefinition['type']) {
            QuantityPriceDefinition::TYPE => QuantityPriceDefinition::fromArray($priceDefinition),
            AbsolutePriceDefinition::TYPE => new AbsolutePriceDefinition((float) $priceDefinition['price']),
            PercentagePriceDefinition::TYPE => new PercentagePriceDefinition($priceDefinition['percentage']),
            default => throw CartException::invalidPriceFieldTypeException($priceDefinition['type']),
        };
    }
}

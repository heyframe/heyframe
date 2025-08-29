<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Hook\Pricing;

use HeyFrame\Core\Checkout\Cart\Facade\PriceFacade;
use HeyFrame\Core\Checkout\Cart\Facade\ScriptPriceStubs;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\PriceCollection as CalculatedPriceCollection;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Content\Product\ProductException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\Price;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * The PriceCollectionFacade is a wrapper around the calculated price collection of a product. It allows to manipulate the quantity
 * prices by resetting or changing the price collection.
 *
 * @script-service product
 *
 * @implements \IteratorAggregate<PriceFacade>
 */
#[Package('inventory')]
class PriceCollectionFacade implements \IteratorAggregate, \Countable
{
    public function __construct(
        private readonly Entity                    $product,
        private readonly CalculatedPriceCollection $prices,
        private readonly ScriptPriceStubs          $priceStubs,
        private readonly ChannelContext            $context
    )
    {
    }

    /**
     * The `reset()` functions allows to reset the complete price collection.
     */
    public function reset(): void
    {
        $this->prices->clear();
    }

    /**
     * The `change()` function allows a complete overwrite of the product quantity prices
     *
     * @param list<array{to: int|null, price: PriceCollection}> $changes
     *
     * @example pricing-cases/product-pricing.twig 40 5 Overwrite the product prices with a new quantity price graduation
     */
    public function change(array $changes): void
    {
        $mapped = [];
        foreach ($changes as $change) {
            $mapped[(string)$change['to']] = $change['price'];
        }

        // check for "null" value
        if (!\array_key_exists('', $mapped)) {
            throw ProductException::invalidPriceDefinition();
        }

        $last = $mapped[null];
        unset($mapped[null]);

        \ksort($mapped, \SORT_NUMERIC);
        $arrayKeys = \array_keys($mapped);
        \assert(!empty($arrayKeys));
        $max = \max($arrayKeys);

        $mapped[$max + 1] = $last;

        $this->prices->clear();


        foreach ($mapped as $quantity => $price) {
            $value = $this->getPriceForTaxState($price, $this->context);

            $definition = new QuantityPriceDefinition($value, $quantity);

            $this->prices->add(
                $this->priceStubs->calculateQuantity($definition, $this->context)
            );
        }
    }

    /**
     * @internal
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator(
            $this->prices->map(function (CalculatedPrice $price) {
                return new PriceFacade($this->product, $price, $this->priceStubs, $this->context);
            })
        );
    }

    /**
     * The `count()` function returns the number of prices which are stored inside this collection.
     *
     * @return int Returns the number of prices which are stored inside this collection
     */
    public function count(): int
    {
        return $this->prices->count();
    }

    private function getPriceForTaxState(PriceCollection $price, ChannelContext $context): float
    {
        $currency = $price->getCurrencyPrice($this->context->getCurrencyId());

        if (!$currency instanceof Price) {
            throw ProductException::invalidPriceDefinition();
        }

        return $currency->getGross();
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Cart\Discount\Calculator;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\Price\AbsolutePriceCalculator;
use HeyFrame\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use HeyFrame\Core\Checkout\Cart\Price\Struct\PriceCollection;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\Composition\DiscountCompositionItem;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountCalculatorResult;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountLineItem;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use HeyFrame\Core\Checkout\Promotion\PromotionException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class DiscountFixedPriceCalculator
{
    public function __construct(private readonly AbsolutePriceCalculator $absolutePriceCalculator)
    {
    }

    /**
     * @throws PromotionException
     * @throws CartException
     */
    public function calculate(DiscountLineItem $discount, DiscountPackageCollection $packages, ChannelContext $context): DiscountCalculatorResult
    {
        $priceDefinition = $discount->getPriceDefinition();

        if (!$priceDefinition instanceof AbsolutePriceDefinition) {
            throw PromotionException::invalidPriceDefinition($discount->getLabel(), $discount->getCode());
        }

        $fixedTotalPrice = abs($priceDefinition->getPrice());

        $affectedPrices = $packages->getAffectedPrices();

        $discountDiff = $this->getTotalDiscountDiffSum($fixedTotalPrice, $packages, $affectedPrices);

        // now calculate the correct price
        // from our collected total discount price
        $discountPrice = $this->absolutePriceCalculator->calculate(
            -abs($discountDiff),
            $affectedPrices,
            $context
        );

        $composition = $this->getCompositionItems(
            $discountPrice->getTotalPrice(),
            $packages,
            $affectedPrices
        );

        return new DiscountCalculatorResult($discountPrice, $composition);
    }

    private function getTotalDiscountDiffSum(float $fixedPackagePrice, DiscountPackageCollection $packages, PriceCollection $affectedPrices): float
    {
        return $affectedPrices->getTotalPriceAmount() - ($fixedPackagePrice * $packages->count());
    }

    /**
     * @return list<DiscountCompositionItem>
     */
    private function getCompositionItems(float $discountValue, DiscountPackageCollection $packages, PriceCollection $affectedPrices): array
    {
        $totalOriginalSum = $affectedPrices->getTotalPriceAmount();

        $items = [];

        foreach ($packages as $package) {
            foreach ($package->getCartItems() as $lineItem) {
                if ($lineItem->getPrice() === null) {
                    continue;
                }

                $itemTotal = $lineItem->getPrice()->getTotalPrice();

                $factor = $itemTotal / $totalOriginalSum;

                $items[] = new DiscountCompositionItem(
                    $lineItem->getId(),
                    $lineItem->getQuantity(),
                    abs($discountValue) * $factor
                );
            }
        }

        return $items;
    }
}

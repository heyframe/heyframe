<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Cart\Discount\Calculator;

use HeyFrame\Core\Checkout\Cart\Price\AbsolutePriceCalculator;
use HeyFrame\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\Composition\DiscountCompositionItem;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountCalculatorInterface;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountCalculatorResult;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountLineItem;
use HeyFrame\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use HeyFrame\Core\Checkout\Promotion\PromotionException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class DiscountAbsoluteCalculator implements DiscountCalculatorInterface
{
    public function __construct(private readonly AbsolutePriceCalculator $priceCalculator)
    {
    }

    /**
     * @throws PromotionException
     */
    public function calculate(DiscountLineItem $discount, DiscountPackageCollection $packages, ChannelContext $context): DiscountCalculatorResult
    {
        /** @var AbsolutePriceDefinition $definition */
        $definition = $discount->getPriceDefinition();

        if (!$definition instanceof AbsolutePriceDefinition) {
            throw PromotionException::invalidPriceDefinition($discount->getLabel(), $discount->getCode());
        }

        $affectedPrices = $packages->getAffectedPrices();

        $totalOriginalSum = $affectedPrices->getTotalPriceAmount();
        $discountValue = -min(abs($definition->getPrice()), $totalOriginalSum);

        $price = $this->priceCalculator->calculate(
            $discountValue,
            $affectedPrices,
            $context
        );

        $composition = $this->getCompositionItems(
            $discountValue,
            $packages,
            $totalOriginalSum
        );

        return new DiscountCalculatorResult($price, $composition);
    }

    /**
     * @return list<DiscountCompositionItem>
     */
    private function getCompositionItems(float $discountValue, DiscountPackageCollection $packages, float $totalOriginalSum): array
    {
        $items = [];

        foreach ($packages as $package) {
            foreach ($package->getCartItems() as $lineItem) {
                if ($lineItem->getPrice() === null) {
                    continue;
                }

                $itemTotal = $lineItem->getPrice()->getTotalPrice();

                $factor = $totalOriginalSum === 0.0 ? 0 : $itemTotal / $totalOriginalSum;

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

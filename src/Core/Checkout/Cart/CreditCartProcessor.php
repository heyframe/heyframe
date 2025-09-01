<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Checkout\Cart\LineItem\CartDataCollection;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\Price\AbsolutePriceCalculator;
use HeyFrame\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class CreditCartProcessor implements CartProcessorInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly AbsolutePriceCalculator $calculator)
    {
    }

    public function process(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        ChannelContext $context,
        CartBehavior $behavior
    ): void {
        $lineItems = $original->getLineItems()->filterType(LineItem::CREDIT_LINE_ITEM_TYPE);

        foreach ($lineItems as $lineItem) {
            $definition = $lineItem->getPriceDefinition();

            if (!$definition instanceof AbsolutePriceDefinition) {
                continue;
            }

            $lineItem->setPrice(
                $this->calculator->calculate(
                    $definition->getPrice(),
                    $toCalculate->getLineItems()->getPrices(),
                    $context
                )
            );
            $toCalculate->add($lineItem);
        }
    }
}

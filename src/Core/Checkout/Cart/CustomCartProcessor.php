<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Checkout\Cart\Delivery\Struct\DeliveryInformation;
use HeyFrame\Core\Checkout\Cart\LineItem\CartDataCollection;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use HeyFrame\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use HeyFrame\Core\Content\Product\State;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class CustomCartProcessor implements CartProcessorInterface, CartDataCollectorInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly QuantityPriceCalculator $calculator)
    {
    }

    public function collect(
        CartDataCollection $data,
        Cart $original,
        ChannelContext $context,
        CartBehavior $behavior
    ): void {
        $lineItems = $original
            ->getLineItems()
            ->filterFlatByType(LineItem::CUSTOM_LINE_ITEM_TYPE);

        foreach ($lineItems as $lineItem) {
            $this->enrich($lineItem);
        }
    }

    public function process(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        ChannelContext $context,
        CartBehavior $behavior
    ): void {
        $lineItems = $original->getLineItems()->filterType(LineItem::CUSTOM_LINE_ITEM_TYPE);

        foreach ($lineItems as $lineItem) {
            $definition = $lineItem->getPriceDefinition();

            if (!$definition instanceof QuantityPriceDefinition) {
                continue;
            }

            $lineItem->setPrice(
                $this->calculator->calculate(
                    $definition,
                    $context
                )
            );

            $lineItem->setShippingCostAware(!$lineItem->hasState(State::IS_DOWNLOAD));

            $toCalculate->add($lineItem);
        }
    }

    private function enrich(LineItem $lineItem): void
    {
        if ($lineItem->getDeliveryInformation() !== null) {
            return;
        }

        $lineItem->setDeliveryInformation(new DeliveryInformation($lineItem->getQuantity(), 0, false));
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Facade;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartBehavior;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItemFactoryRegistry;
use HeyFrame\Core\Checkout\Cart\Processor;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @internal
 */
#[Package('checkout')]
class CartFacadeHelper
{
    /**
     * @internal
     */
    public function __construct(
        private readonly LineItemFactoryRegistry $factory,
        private readonly Processor $processor
    ) {
    }

    public function product(string $productId, int $quantity, ChannelContext $context): LineItem
    {
        $data = [
            'type' => LineItem::PRODUCT_LINE_ITEM_TYPE,
            'id' => $productId,
            'referencedId' => $productId,
            'quantity' => $quantity,
        ];

        return $this->factory->create($data, $context);
    }

    public function calculate(Cart $cart, CartBehavior $behavior, ChannelContext $context): Cart
    {
        return $this->processor->process($cart, $context, $behavior);
    }
}

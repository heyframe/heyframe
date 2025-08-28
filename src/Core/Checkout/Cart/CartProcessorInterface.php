<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Checkout\Cart\LineItem\CartDataCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
interface CartProcessorInterface
{
    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, ChannelContext $context, CartBehavior $behavior): void;
}

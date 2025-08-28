<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Checkout\Cart\LineItem\CartDataCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
interface CartDataCollectorInterface
{
    public function collect(CartDataCollection $data, Cart $original, ChannelContext $context, CartBehavior $behavior): void;
}

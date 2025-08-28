<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Channel;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * This route can be used to create an order from the cart
 */
#[Package('checkout')]
abstract class AbstractCartOrderRoute
{
    abstract public function getDecorated(): AbstractCartOrderRoute;

    abstract public function order(Cart $cart, ChannelContext $context, RequestDataBag $data): CartOrderRouteResponse;
}

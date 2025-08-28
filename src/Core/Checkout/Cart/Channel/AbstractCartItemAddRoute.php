<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Channel;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route can be used to add new line items to the cart
 */
#[Package('checkout')]
abstract class AbstractCartItemAddRoute
{
    abstract public function getDecorated(): AbstractCartItemAddRoute;

    /**
     * @param array<LineItem>|null $items
     */
    abstract public function add(Request $request, Cart $cart, ChannelContext $context, ?array $items): CartResponse;
}

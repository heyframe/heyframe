<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Channel;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * The 'AbstractCartItemUpdateRoute' is responsible for updating the data of a line item.
 * Internally the LineItemFactory is addressed for this purpose, where each line item type is handled individually.
 * After the line item has been updated, the cart is recalculated, then saved under the current token and returned calculated.
 */
#[Package('checkout')]
abstract class AbstractCartItemUpdateRoute
{
    abstract public function getDecorated(): AbstractCartItemUpdateRoute;

    abstract public function change(Request $request, Cart $cart, ChannelContext $context): CartResponse;
}

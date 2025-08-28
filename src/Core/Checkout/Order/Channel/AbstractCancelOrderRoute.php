<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route is used to cancel a order
 */
#[Package('checkout')]
abstract class AbstractCancelOrderRoute
{
    abstract public function getDecorated(): AbstractCancelOrderRoute;

    abstract public function cancel(Request $request, ChannelContext $context): CancelOrderRouteResponse;
}

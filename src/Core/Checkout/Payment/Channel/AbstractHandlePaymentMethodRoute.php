<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route can be used to handle the payment for an order.
 */
#[Package('checkout')]
abstract class AbstractHandlePaymentMethodRoute
{
    abstract public function getDecorated(): AbstractHandlePaymentMethodRoute;

    abstract public function load(Request $request, ChannelContext $context): HandlePaymentMethodRouteResponse;
}

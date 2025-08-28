<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route is used to update the paymentMethod for an order
 */
#[Package('checkout')]
abstract class AbstractSetPaymentOrderRoute
{
    abstract public function getDecorated(): AbstractSetPaymentOrderRoute;

    abstract public function setPayment(Request $request, ChannelContext $context): SetPaymentOrderRouteResponse;
}

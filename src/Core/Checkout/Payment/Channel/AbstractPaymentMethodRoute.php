<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route can be used to load all payment methods of the authenticated sales channel.
 * With this route it is also possible to send the standard API parameters such as: 'page', 'limit', 'filter', etc.
 */
#[Package('checkout')]
abstract class AbstractPaymentMethodRoute
{
    abstract public function getDecorated(): AbstractPaymentMethodRoute;

    abstract public function load(Request $request, ChannelContext $context, Criteria $criteria): PaymentMethodRouteResponse;
}

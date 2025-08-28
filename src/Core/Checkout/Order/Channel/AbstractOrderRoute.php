<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Channel;

use HeyFrame\Core\Checkout\Cart\Exception\CustomerNotLoggedInException;
use HeyFrame\Core\Checkout\Order\Exception\GuestNotAuthenticatedException;
use HeyFrame\Core\Checkout\Order\Exception\WrongGuestCredentialsException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route is used to load the orders of the logged-in customer
 * With this route it is also possible to send the standard API parameters such as: 'page', 'limit', 'filter', etc.
 */
#[Package('checkout')]
abstract class AbstractOrderRoute
{
    abstract public function getDecorated(): AbstractOrderRoute;

    /**
     * @throws CustomerNotLoggedInException
     * @throws GuestNotAuthenticatedException
     * @throws WrongGuestCredentialsException
     */
    abstract public function load(Request $request, ChannelContext $context, Criteria $criteria): OrderRouteResponse;
}

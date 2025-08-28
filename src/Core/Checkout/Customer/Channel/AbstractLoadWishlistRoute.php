<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
abstract class AbstractLoadWishlistRoute
{
    abstract public function getDecorated(): AbstractLoadWishlistRoute;

    abstract public function load(Request $request, ChannelContext $context, Criteria $criteria, CustomerEntity $customer): LoadWishlistRouteResponse;
}

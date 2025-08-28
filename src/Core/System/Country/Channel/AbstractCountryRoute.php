<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Country\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route can be used to load all countries of the authenticated sales channel.
 * With this route it is also possible to send the standard API parameters such as: 'page', 'limit', 'filter', etc.
 */
#[Package('fundamentals@discovery')]
abstract class AbstractCountryRoute
{
    abstract public function load(Request $request, Criteria $criteria, ChannelContext $context): CountryRouteResponse;

    abstract protected function getDecorated(): AbstractCountryRoute;
}

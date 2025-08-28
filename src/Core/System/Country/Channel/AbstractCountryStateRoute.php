<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Country\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route can be used to load all states of a given country.
 * With this route it is also possible to send the standard API parameters such as: 'page', 'limit', 'filter', etc.
 */
#[Package('fundamentals@discovery')]
abstract class AbstractCountryStateRoute
{
    abstract public function load(string $countryId, Request $request, Criteria $criteria, ChannelContext $context): CountryStateRouteResponse;

    abstract protected function getDecorated(): AbstractCountryStateRoute;
}

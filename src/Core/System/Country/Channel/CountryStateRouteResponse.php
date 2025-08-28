<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Country\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;
use HeyFrame\Core\System\Country\Aggregate\CountryState\CountryStateCollection;

/**
 * @extends StoreApiResponse<EntitySearchResult<CountryStateCollection>>
 */
#[Package('fundamentals@discovery')]
class CountryStateRouteResponse extends StoreApiResponse
{
    public function getStates(): CountryStateCollection
    {
        return $this->object->getEntities();
    }
}

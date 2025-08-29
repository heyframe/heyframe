<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Country\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\FrontApiResponse;
use HeyFrame\Core\System\Country\Aggregate\CountryState\CountryStateCollection;

/**
 * @extends FrontApiResponse<EntitySearchResult<CountryStateCollection>>
 */
#[Package('fundamentals@discovery')]
class CountryStateRouteResponse extends FrontApiResponse
{
    public function getStates(): CountryStateCollection
    {
        return $this->object->getEntities();
    }
}

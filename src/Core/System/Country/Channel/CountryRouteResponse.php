<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Country\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;
use HeyFrame\Core\System\Country\CountryCollection;

/**
 * @extends StoreApiResponse<EntitySearchResult<CountryCollection>>
 */
#[Package('fundamentals@discovery')]
class CountryRouteResponse extends StoreApiResponse
{
    /**
     * @return EntitySearchResult<CountryCollection>
     */
    public function getResult(): EntitySearchResult
    {
        return $this->object;
    }

    public function getCountries(): CountryCollection
    {
        return $this->object->getEntities();
    }
}

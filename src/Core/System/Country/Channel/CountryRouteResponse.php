<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Country\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\FrontApiResponse;
use HeyFrame\Core\System\Country\CountryCollection;

/**
 * @extends FrontApiResponse<EntitySearchResult<CountryCollection>>
 */
#[Package('fundamentals@discovery')]
class CountryRouteResponse extends FrontApiResponse
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

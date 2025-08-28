<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Language\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;
use HeyFrame\Core\System\Language\LanguageCollection;

/**
 * @extends StoreApiResponse<EntitySearchResult<LanguageCollection>>
 */
#[Package('fundamentals@discovery')]
class LanguageRouteResponse extends StoreApiResponse
{
    public function getLanguages(): LanguageCollection
    {
        return $this->object->getEntities();
    }
}

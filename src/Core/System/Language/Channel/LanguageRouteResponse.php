<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Language\Channel;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\FrontApiResponse;
use HeyFrame\Core\System\Language\LanguageCollection;

/**
 * @extends FrontApiResponse<EntitySearchResult<LanguageCollection>>
 */
#[Package('fundamentals@discovery')]
class LanguageRouteResponse extends FrontApiResponse
{
    public function getLanguages(): LanguageCollection
    {
        return $this->object->getEntities();
    }
}

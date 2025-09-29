<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\Channel;

use HeyFrame\Core\Content\Seo\SeoUrl\SeoUrlCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<EntitySearchResult<SeoUrlCollection>>
 */
#[Package('inventory')]
class SeoUrlRouteResponse extends StoreApiResponse
{
    public function getSeoUrls(): SeoUrlCollection
    {
        return $this->object->getEntities();
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Channel;

use HeyFrame\Core\Content\Category\CategoryCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\FrontApiResponse;

/**
 * @extends FrontApiResponse<EntitySearchResult<CategoryCollection>>
 */
#[Package('discovery')]
class CategoryListRouteResponse extends FrontApiResponse
{
    public function getCategories(): CategoryCollection
    {
        return $this->object->getEntities();
    }
}

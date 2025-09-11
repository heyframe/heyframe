<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Channel;

use HeyFrame\Core\Content\Category\CategoryCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<CategoryCollection>
 */
#[Package('discovery')]
class NavigationRouteResponse extends StoreApiResponse
{
    public function getCategories(): CategoryCollection
    {
        return $this->object;
    }
}

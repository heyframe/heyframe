<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Channel;

use HeyFrame\Core\Content\Category\CategoryEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\FrontApiResponse;

/**
 * @extends FrontApiResponse<CategoryEntity>
 */
#[Package('discovery')]
class CategoryRouteResponse extends FrontApiResponse
{
    public function getCategory(): CategoryEntity
    {
        return $this->object;
    }
}

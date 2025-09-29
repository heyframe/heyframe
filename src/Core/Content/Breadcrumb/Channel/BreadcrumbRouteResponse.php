<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Breadcrumb\Channel;

use HeyFrame\Core\Content\Breadcrumb\Struct\BreadcrumbCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<BreadcrumbCollection>
 */
#[Package('inventory')]
class BreadcrumbRouteResponse extends StoreApiResponse
{
    public function getBreadcrumbCollection(): BreadcrumbCollection
    {
        return $this->object;
    }
}

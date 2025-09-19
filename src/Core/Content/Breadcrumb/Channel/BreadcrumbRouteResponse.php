<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Breadcrumb\Channel;

use HeyFrame\Core\Content\Breadcrumb\Struct\BreadcrumbCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\FrontApiResponse;

/**
 * @extends FrontApiResponse<BreadcrumbCollection>
 */
#[Package('inventory')]
class BreadcrumbRouteResponse extends FrontApiResponse
{
    public function getBreadcrumbCollection(): BreadcrumbCollection
    {
        return $this->object;
    }
}

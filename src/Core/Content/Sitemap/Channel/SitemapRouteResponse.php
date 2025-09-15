<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Channel;

use HeyFrame\Core\Content\Sitemap\Struct\SitemapCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<SitemapCollection>
 */
#[Package('discovery')]
class SitemapRouteResponse extends StoreApiResponse
{
    public function getSitemaps(): SitemapCollection
    {
        return $this->object;
    }
}

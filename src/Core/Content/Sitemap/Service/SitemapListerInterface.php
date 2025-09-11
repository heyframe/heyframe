<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Service;

use HeyFrame\Core\Content\Sitemap\Struct\Sitemap;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('discovery')]
interface SitemapListerInterface
{
    /**
     * @return Sitemap[]
     */
    public function getSitemaps(ChannelContext $channelContext): array;
}

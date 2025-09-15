<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('discovery')]
abstract class AbstractSitemapRoute
{
    abstract public function load(Request $request, ChannelContext $context): SitemapRouteResponse;

    abstract public function getDecorated(): AbstractSitemapRoute;
}

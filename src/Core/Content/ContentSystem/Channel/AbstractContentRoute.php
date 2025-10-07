<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route can be used to load content pages based on URL patterns.
 * It matches incoming URLs against content routes stored in the database,
 * resolves entity IDs, and determines the appropriate content layout.
 */
#[Package('discovery')]
abstract class AbstractContentRoute
{
    abstract public function getDecorated(): AbstractContentRoute;

    abstract public function load(string $path, Request $request, ChannelContext $context): ContentRouteResponse;
}

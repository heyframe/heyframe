<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('discovery')]
abstract class AbstractLoadNavigationRoute
{
    abstract public function getDecorated(): AbstractLoadNavigationRoute;

    abstract public function load(string $navigationId, Request $request, ChannelContext $context): LoadNavigationRouteResponse;
}

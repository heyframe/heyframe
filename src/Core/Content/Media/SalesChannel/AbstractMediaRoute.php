<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\SalesChannel;

use HeyFrame\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractMediaRoute
{
    abstract public function getDecorated(): AbstractMediaRoute;

    abstract public function load(Request $request, SalesChannelContext $context): MediaRouteResponse;
}

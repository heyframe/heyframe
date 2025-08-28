<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('discovery')]
abstract class AbstractMediaRoute
{
    abstract public function getDecorated(): AbstractMediaRoute;

    abstract public function load(Request $request, ChannelContext $context): MediaRouteResponse;
}

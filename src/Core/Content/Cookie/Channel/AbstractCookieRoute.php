<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Cookie\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;


#[Package('framework')]
abstract class AbstractCookieRoute
{
    abstract public function getDecorated(): AbstractCookieRoute;

    abstract public function getCookieGroups(Request $request, ChannelContext $channelContext): CookieRouteResponse;
}

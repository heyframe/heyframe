<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Routing;

use HeyFrame\Core\ChannelRequest;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\AbstractRouteScope;
use HeyFrame\Core\Framework\Routing\ChannelContextRouteScopeDependant;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
class FrontendRouteScope extends AbstractRouteScope implements ChannelContextRouteScopeDependant
{
    final public const ID = 'frontend';

    public function isAllowed(Request $request): bool
    {
        return $request->attributes->has(ChannelRequest::ATTRIBUTE_IS_CHANNEL_REQUEST)
            && $request->attributes->get(ChannelRequest::ATTRIBUTE_IS_CHANNEL_REQUEST) === true
        ;
    }

    public function getId(): string
    {
        return self::ID;
    }
}

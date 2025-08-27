<?php declare(strict_types=1);

namespace HeyFrame\Core;

final class PlatformRequest
{
    /**
     * Context attributes
     */
    public const ATTRIBUTE_CONTEXT_OBJECT = 'sw-context';
    public const ATTRIBUTE_CHANNEL_CONTEXT_OBJECT = 'sw-channel-context';
    public const ATTRIBUTE_CHANNEL_ID = 'sw-channel-id';
    public const ATTRIBUTE_ACL = '_acl';
    public const ATTRIBUTE_ROUTE_SCOPE = '_routeScope';
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Routing;

use HeyFrame\Core\Content\ContentSystem\Routing\Matcher\ContentRouteMatcher;
use HeyFrame\Core\Content\ContentSystem\Routing\RouteCollection\RouteCollectionBuilder;
use HeyFrame\Core\Content\ContentSystem\Routing\Struct\RouteMatchResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('discovery')]
class ContentRouter
{
    /**
     * @internal
     */
    public function __construct(
        protected readonly RouteCollectionBuilder $routeCollectionBuilder,
        protected readonly ContentRouteMatcher $matcher
    ) {
    }

    public function match(string $pathInfo, ChannelContext $context): ?RouteMatchResult
    {
        $routes = $this->routeCollectionBuilder->build($context);

        return $this->matcher->match($pathInfo, $routes);
    }
}

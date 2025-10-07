<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Routing\Matcher;

use HeyFrame\Core\Content\ContentSystem\ContentRoute\ContentRouteEntity;
use HeyFrame\Core\Content\ContentSystem\Routing\Struct\RouteMatchResult;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

#[Package('discovery')]
class ContentRouteMatcher
{
    public function match(string $pathInfo, RouteCollection $routes): ?RouteMatchResult
    {
        $context = new RequestContext();
        $context->setPathInfo($pathInfo);

        $matcher = new UrlMatcher($routes, $context);

        try {
            $parameters = $matcher->match($pathInfo);
        } catch (ResourceNotFoundException) {
            return null;
        }

        /** @var ContentRouteEntity|null $contentRoute */
        $contentRoute = $parameters['_content_route'] ?? null;

        if (!$contentRoute instanceof ContentRouteEntity) {
            return null;
        }

        // Remove internal routing parameters
        unset($parameters['_content_route'], $parameters['_content_route_id'], $parameters['_route']);

        return new RouteMatchResult($contentRoute, $parameters);
    }
}

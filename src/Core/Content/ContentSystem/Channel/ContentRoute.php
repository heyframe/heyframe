<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Channel;

use HeyFrame\Core\Content\ContentSystem\Compilation\ContentPageBuilder;
use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Content\ContentSystem\Hydration\HydrationService;
use HeyFrame\Core\Content\ContentSystem\Resolver\EntityIdResolver;
use HeyFrame\Core\Content\ContentSystem\Resolver\LayoutResolver;
use HeyFrame\Core\Content\ContentSystem\Routing\ContentRouter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('discovery')]
class ContentRoute extends AbstractContentRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ContentRouter $contentRouter,
        private readonly EntityIdResolver $entityIdResolver,
        private readonly LayoutResolver $layoutResolver,
        private readonly ContentPageBuilder $contentPageBuilder,
        private readonly HydrationService $hydrationService
    ) {
    }

    public function getDecorated(): AbstractContentRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/content/{path}',
        name: 'store-api.content.detail',
        requirements: ['path' => '.+'],
        defaults: ['_httpCache' => true],
        methods: ['GET', 'POST']
    )]
    public function load(string $path, Request $request, ChannelContext $context): ContentRouteResponse
    {
        // Normalize path (ensure leading slash)
        $pathInfo = '/' . ltrim($path, '/');

        // Phase 1: Match content route
        $match = $this->contentRouter->match($pathInfo, $context);

        if ($match === null) {
            throw ContentSystemException::routeNotFound($pathInfo);
        }

        $route = $match->getRoute();

        // Phase 2: Resolve entity IDs
        $resolvedData = $this->entityIdResolver->resolve($match, $context);

        if ($resolvedData === null) {
            throw ContentSystemException::entityNotResolved('unknown', $pathInfo);
        }

        // Phase 2: Resolve layout ID
        $layoutId = $route->getLayoutId();

        if ($layoutId === null) {
            // Use dynamic layout resolution
            $layoutId = $this->layoutResolver->resolve($match, $resolvedData, $context);

            if ($layoutId === null) {
                throw ContentSystemException::layoutNotResolved('unknown', 'unknown');
            }

            $resolvedData->setResolvedLayoutId($layoutId);
        }

        // Phase 3: Build content page
        $contentPage = $this->contentPageBuilder->build($layoutId, $resolvedData, $context->getContext());

        if ($contentPage === null) {
            throw ContentSystemException::layoutNotResolved('unknown', 'unknown');
        }

        // Set route and matched parameters
        $contentPage->setRoute($route);

        // Phase 4: Hydrate entities
        $this->hydrationService->hydrate($contentPage, $context->getContext());

        return new ContentRouteResponse($contentPage);
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Channel;

use HeyFrame\Core\Content\ContentSystem\Compilation\ContentPageBuilder;
use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Content\ContentSystem\Hydration\HydrationService;
use HeyFrame\Core\Content\ContentSystem\Resolver\EntityIdResolver;
use HeyFrame\Core\Content\ContentSystem\Resolver\LayoutResolver;
use HeyFrame\Core\Content\ContentSystem\Response\ContentResponseGenerator;
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
        private readonly HydrationService $hydrationService,
        private readonly ContentResponseGenerator $responseGenerator
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
        $pathInfo = '/' . ltrim($path, '/');

        $match = $this->contentRouter->match($pathInfo, $context);

        if ($match === null) {
            // SOFT ERROR: No route matches this URL
            throw ContentSystemException::contentNotFound($pathInfo);
        }

        $route = $match->getRoute();

        try {
            $resolvedData = $this->entityIdResolver->resolve($match, $context);
        } catch (\Throwable $e) {
            // HARD ERROR: Resolution process failed unexpectedly
            throw ContentSystemException::resolutionFailed($route->getName(), $e->getMessage(), $e);
        }

        if ($resolvedData === null) {
            // SOFT ERROR: Entity doesn't exist or constraints not satisfied
            $parameterBinding = $route->getParameterBinding();
            $parameters = $match->getParameters();

            $firstParam = array_key_first($parameterBinding);
            if ($firstParam !== null) {
                $paramConfig = $parameterBinding[$firstParam];
                $entityType = $paramConfig['resolution']['entity'] ?? 'entity';
                $matchField = $paramConfig['resolution']['match_field'] ?? 'id';
                $value = $parameters[$firstParam] ?? 'unknown';

                throw ContentSystemException::entityNotFound($entityType, $value, $matchField);
            }

            throw ContentSystemException::entityNotFound('entity', $pathInfo, 'path');
        }

        $layoutId = $route->getLayoutId();

        if ($layoutId === null) {
            try {
                $layoutId = $this->layoutResolver->resolve($match, $resolvedData, $context);
            } catch (\Throwable $e) {
                // HARD ERROR: Layout resolution failed unexpectedly
                throw ContentSystemException::resolutionFailed($route->getName(), $e->getMessage(), $e);
            }

            if ($layoutId === null) {
                // SOFT ERROR: No layout assigned to this entity
                $entityIds = $resolvedData->getEntityIds();
                $entityIdsArray = $entityIds->toArray();
                $firstEntityKey = array_key_first($entityIdsArray);
                $entityType = $firstEntityKey !== null ? str_replace('_id', '', $firstEntityKey) : 'entity';
                $entityId = $firstEntityKey !== null ? $entityIdsArray[$firstEntityKey] : 'unknown';

                throw ContentSystemException::layoutAssignmentNotFound(
                    $entityType,
                    $entityId,
                    $context->getChannel()->getId()
                );
            }

            $resolvedData->setResolvedLayoutId($layoutId);
        }

        try {
            $contentPage = $this->contentPageBuilder->build($layoutId, $resolvedData, $context);
        } catch (\Throwable $e) {
            // HARD ERROR: Page building failed
            throw ContentSystemException::pageBuildingFailed($layoutId, $e->getMessage(), $e);
        }

        if ($contentPage === null) {
            // HARD ERROR: Layout doesn't exist (configuration error)
            throw ContentSystemException::layoutNotFound($layoutId);
        }

        $contentPage->setRoute($route);

        try {
            $this->hydrationService->hydrate($contentPage, $context);
        } catch (\Throwable $e) {
            // HARD ERROR: Hydration failed
            throw ContentSystemException::hydrationFailed($e->getMessage(), $e);
        }

        $response = $this->responseGenerator->generate($contentPage, $context);
        $contentPage->assign(['response' => $response]);

        return new ContentRouteResponse($contentPage);
    }
}

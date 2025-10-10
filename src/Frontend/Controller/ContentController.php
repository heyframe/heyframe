<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Controller;

use HeyFrame\Core\Content\ContentSystem\Channel\AbstractContentRoute;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Frontend\Framework\Routing\FrontendRouteScope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @internal
 * Do not use direct or indirect repository calls in a controller. Always use a store-api route to get or put data
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontendRouteScope::ID]])]
#[Package('discovery')]
class ContentController extends FrontendController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractContentRoute $contentRoute
    ) {
    }

    #[Route(
        path: '/{path}',
        name: 'frontend.content.page',
        requirements: ['path' => '.+'],
        defaults: ['_httpCache' => true],
        methods: ['GET'],
        priority: -100
    )]
    public function index(Request $request, ChannelContext $context): Response
    {
        $pathInfo = $request->getPathInfo();
        $response = $this->contentRoute->load($pathInfo, $request, $context);
        $contentPage = $response->getDecomposedContentPage();

        return $this->renderFrontend('@Frontend/frontend/page/content/index.html.twig', [
            'contentPage' => $contentPage,
        ]);
    }
}

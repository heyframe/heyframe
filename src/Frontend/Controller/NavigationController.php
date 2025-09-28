<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Controller;

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
class NavigationController extends FrontendController
{
    #[Route(
        path: '/',
        name: 'frontend.home.page',
        defaults: ['_httpCache' => true],
        methods: ['GET'],
    )]
    public function home(Request $request, ChannelContext $context): Response
    {
        return $this->renderFrontend('@Frontend/frontend/page/content/index.html.twig');
    }
}

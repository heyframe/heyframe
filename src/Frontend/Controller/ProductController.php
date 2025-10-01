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
 * Do not use direct or indirect repository calls in a controller. Always use a front-api route to get or put data
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontendRouteScope::ID]])]
#[Package('framework')]
class ProductController extends FrontendController
{
    #[Route(path: '/detail/{productId}', name: 'frontend.detail.page', defaults: ['_httpCache' => true], methods: ['GET'])]
    public function index(ChannelContext $context, Request $request): Response
    {
        return new Response();
    }
}

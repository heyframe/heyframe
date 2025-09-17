<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Cookie\Channel;

use HeyFrame\Core\Content\Cookie\Service\CookieProvider;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('framework')]
class CookieRoute extends AbstractCookieRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly CookieProvider $cookieProvider,
    ) {
    }

    public function getDecorated(): AbstractCookieRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/cookie-groups', name: 'front-api.cookie.groups', methods: [Request::METHOD_GET])]
    public function getCookieGroups(Request $request, ChannelContext $channelContext): CookieRouteResponse
    {
        $cookieGroups = $this->cookieProvider->getCookieGroups($channelContext);

        return new CookieRouteResponse($cookieGroups);
    }
}

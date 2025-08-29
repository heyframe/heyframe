<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Gateway\Context\Channel;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Framework\App\Context\Gateway\AppContextGateway;
use HeyFrame\Core\Framework\Gateway\Context\Command\Struct\ContextGatewayPayloadStruct;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ContextTokenResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('framework')]
class ContextGatewayRoute extends AbstractContextGatewayRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AppContextGateway $contextGateway,
    ) {
    }

    public function getDecorated(): AbstractContextGatewayRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/context/gateway', name: 'store-api.context.gateway', methods: ['GET', 'POST'])]
    public function load(Request $request, Cart $cart, ChannelContext $context): ContextTokenResponse
    {
        return $this->contextGateway->process(new ContextGatewayPayloadStruct($cart, $context, new RequestDataBag($request->request->all())));
    }
}

<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Channel;

use HeyFrame\Core\Checkout\Cart\AbstractCartPersister;
use HeyFrame\Core\Checkout\Cart\CartCalculator;
use HeyFrame\Core\Checkout\Cart\CartFactory;
use HeyFrame\Core\Checkout\Cart\Exception\CartTokenNotFoundException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('checkout')]
class CartLoadRoute extends AbstractCartLoadRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractCartPersister $persister,
        private readonly CartFactory $cartFactory,
        private readonly CartCalculator $cartCalculator,
    ) {
    }

    public function getDecorated(): AbstractCartLoadRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/checkout/cart', name: 'store-api.checkout.cart.read', methods: ['GET', 'POST'])]
    public function load(Request $request, ChannelContext $context): CartResponse
    {
        $token = $request->get('token', $context->getToken());

        try {
            $cart = $this->persister->load($token, $context);
        } catch (CartTokenNotFoundException) {
            $cart = $this->cartFactory->createNew($token);
        }

        $cart = $this->cartCalculator->calculate($cart, $context);

        return new CartResponse($cart);
    }
}

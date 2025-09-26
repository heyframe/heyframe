<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Channel;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayResponse;
use HeyFrame\Core\Checkout\Payment\Cart\Error\PaymentMethodBlockedError;
use HeyFrame\Core\Checkout\Payment\Channel\AbstractPaymentMethodRoute;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('checkout')]
class CheckoutGatewayRoute extends AbstractCheckoutGatewayRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractPaymentMethodRoute $paymentMethodRoute,
    ) {
    }

    public function getDecorated(): AbstractCheckoutGatewayRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/checkout/gateway', name: 'front-api.checkout.gateway', methods: ['GET', 'POST'])]
    public function load(Request $request, Cart $cart, ChannelContext $context): CheckoutGatewayRouteResponse
    {
        $paymentCriteria = new Criteria();
        $request->query->set('onlyAvailable', '1');

        $paymentMethods = $this->paymentMethodRoute->load($request, $context, $paymentCriteria)->getPaymentMethods();

        $availablePaymentMethods = $paymentMethods;
        $cartErrors = $cart->getErrors();
        $paymentMethod = $context->getPaymentMethod();

        if (!\in_array($paymentMethod->getId(), $availablePaymentMethods->getIds(), true)) {
            $cartErrors->add(
                new PaymentMethodBlockedError((string) $paymentMethod->getTranslation('name'), 'not allowed')
            );
        }

        return new CheckoutGatewayRouteResponse($availablePaymentMethods, $cartErrors);
    }
}

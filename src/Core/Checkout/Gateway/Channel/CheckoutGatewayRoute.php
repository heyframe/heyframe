<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Channel;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayInterface;
use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayResponse;
use HeyFrame\Core\Checkout\Gateway\Command\Struct\CheckoutGatewayPayloadStruct;
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
        private readonly CheckoutGatewayInterface $checkoutGateway,
    ) {
    }

    public function getDecorated(): AbstractCheckoutGatewayRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/checkout/gateway', name: 'store-api.checkout.gateway', methods: ['GET', 'POST'])]
    public function load(Request $request, Cart $cart, ChannelContext $context): CheckoutGatewayRouteResponse
    {
        $paymentCriteria = new Criteria();
        $shippingCriteria = new Criteria();

        $paymentCriteria->addAssociation('appPaymentMethod.app');
        $shippingCriteria->addAssociation('appShippingMethod.app');

        // Only load available payment and shipping methods from the routes
        $request->query->set('onlyAvailable', '1');

        $paymentMethods = $this->paymentMethodRoute->load($request, $context, $paymentCriteria)->getPaymentMethods();

        $payload = new CheckoutGatewayPayloadStruct($cart, $context, $paymentMethods);
        $response = $this->checkoutGateway->process($payload);

        $this->addBlockedMethodsCartErrors($response, $cart, $context);

        return new CheckoutGatewayRouteResponse($response->getAvailablePaymentMethods(), $response->getAvailableShippingMethods(), $response->getCartErrors());
    }

    private function addBlockedMethodsCartErrors(CheckoutGatewayResponse $response, Cart $cart, ChannelContext $context): void
    {
        $paymentMethod = $context->getPaymentMethod();

        if (!\in_array($paymentMethod->getId(), $response->getAvailablePaymentMethods()->getIds(), true)) {
            $response->getCartErrors()->add(
                new PaymentMethodBlockedError((string) $paymentMethod->getTranslation('name'), 'not allowed')
            );
        }
    }
}

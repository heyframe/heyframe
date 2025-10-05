<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Channel;

use HeyFrame\Core\Checkout\Cart\AbstractCartPersister;
use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartCalculator;
use HeyFrame\Core\Checkout\Cart\CartContextHasher;
use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\CartLocker;
use HeyFrame\Core\Checkout\Cart\Event\CheckoutOrderPlacedCriteriaEvent;
use HeyFrame\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use HeyFrame\Core\Checkout\Cart\Extension\CheckoutPlaceOrderExtension;
use HeyFrame\Core\Checkout\Cart\Order\OrderPersisterInterface;
use HeyFrame\Core\Checkout\Cart\Order\OrderPlaceResult;
use HeyFrame\Core\Checkout\Gateway\Channel\AbstractCheckoutGatewayRoute;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Checkout\Payment\PaymentProcessor;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\Extensions\ExtensionDispatcher;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\Profiling\Profiler;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('checkout')]
class CartOrderRoute extends AbstractCartOrderRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<OrderCollection> $orderRepository
     */
    public function __construct(
        private readonly CartCalculator $cartCalculator,
        private readonly EntityRepository $orderRepository,
        private readonly OrderPersisterInterface $orderPersister,
        private readonly AbstractCartPersister $cartPersister,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PaymentProcessor $paymentProcessor,
        private readonly AbstractCheckoutGatewayRoute $checkoutGatewayRoute,
        private readonly CartContextHasher $cartContextHasher,
        private readonly ExtensionDispatcher $extensions,
        private readonly CartLocker $cartLocker
    ) {
    }

    public function getDecorated(): AbstractCartOrderRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/checkout/order', name: 'front-api.checkout.cart.order', defaults: ['_loginRequired' => true, '_loginRequiredAllowGuest' => true], methods: ['POST'])]
    public function order(Cart $cart, ChannelContext $context, RequestDataBag $data): CartOrderRouteResponse
    {
        $hash = $data->getAlnum('hash');

        if ($hash && !$this->cartContextHasher->isMatching($hash, $cart, $context)) {
            throw CartException::hashMismatch($cart->getToken());
        }

        return $this->cartLocker->locked($context, function () use ($cart, $context, $data) {
            // we use this state in stock updater class, to prevent duplicate available stock updates
            $context->addState('checkout-order-route');

            $placed = $this->extensions->publish(
                name: CheckoutPlaceOrderExtension::NAME,
                extension: new CheckoutPlaceOrderExtension($cart, $context, $data),
                function: $this->place(...)
            );

            $orderId = $placed->orderId;

            $this->cartPersister->delete($context->getToken(), $context);

            $criteria = new Criteria([$orderId]);
            $criteria
                ->setTitle('order-route::order-loading')
                ->addAssociation('primaryOrderTransaction')
                ->addAssociation('orderCustomer.customer')
                ->addAssociation('transactions.paymentMethod')
                ->addAssociation('lineItems.cover')
                ->addAssociation('lineItems.downloads.media')
                ->addAssociation('currency')
                ->addAssociation('stateMachineState')
                ->addAssociation('transactions.stateMachineState')
                ->getAssociation('transactions')->addSorting(new FieldSorting('createdAt'));

            $this->eventDispatcher->dispatch(new CheckoutOrderPlacedCriteriaEvent($criteria, $context));

            $orderEntity = Profiler::trace('checkout-order::order-loading', function () use ($criteria, $context): ?OrderEntity {
                return $this->orderRepository->search($criteria, $context->getContext())->getEntities()->first();
            });

            if (!$orderEntity) {
                throw CartException::invalidPaymentOrderNotStored($orderId);
            }

            $event = new CheckoutOrderPlacedEvent($context, $orderEntity);

            Profiler::trace('checkout-order::event-listeners', function () use ($event): void {
                $this->eventDispatcher->dispatch($event);
            });

            return new CartOrderRouteResponse($orderEntity);
        });
    }

    private function place(Cart $cart, ChannelContext $context, RequestDataBag $data): OrderPlaceResult
    {
        $calculatedCart = $this->cartCalculator->calculate($cart, $context);

        $response = $this->checkoutGatewayRoute->load(new Request($data->all(), $data->all()), $cart, $context);
        $calculatedCart->addErrors(...$response->getErrors());

        Profiler::trace('checkout-order::pre-payment', fn () => $this->paymentProcessor->validate($calculatedCart, $data, $context));

        $orderId = Profiler::trace('checkout-order::order-persist', fn () => $this->orderPersister->persist($calculatedCart, $context));

        return new OrderPlaceResult($orderId);
    }
}

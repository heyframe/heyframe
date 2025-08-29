<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Channel;

use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Checkout\Order\OrderException;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('checkout')]
class CancelOrderRoute extends AbstractCancelOrderRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<OrderCollection> $orderRepository
     */
    public function __construct(
        private readonly OrderService $orderService,
        private readonly EntityRepository $orderRepository
    ) {
    }

    public function getDecorated(): AbstractCancelOrderRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/order/state/cancel', name: 'store-api.order.state.cancel', methods: ['POST'], defaults: ['_loginRequired' => true, '_loginRequiredAllowGuest' => true])]
    public function cancel(Request $request, ChannelContext $context): CancelOrderRouteResponse
    {
        $orderId = $request->get('orderId', null);

        if ($orderId === null) {
            throw OrderException::invalidRequestParameter('orderId');
        }

        $this->verify($orderId, $context);

        $newState = $this->orderService->orderStateTransition(
            $orderId,
            'cancel',
            new ParameterBag(),
            $context->getContext()
        );

        return new CancelOrderRouteResponse($newState);
    }

    private function verify(string $orderId, ChannelContext $context): void
    {
        if (!$context->getCustomer()) {
            throw OrderException::customerNotLoggedIn();
        }

        $criteria = (new Criteria([$orderId]))
            ->addFilter(new EqualsFilter('orderCustomer.customerId', $context->getCustomerId()));

        $total = $this->orderRepository->searchIds($criteria, $context->getContext())->getTotal();
        if ($total === 0) {
            throw OrderException::orderNotFound($orderId);
        }
    }
}

<?php
declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Order\Api;

use HeyFrame\Core\Checkout\Cart\AbstractCartPersister;
use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\Order\OrderConverter;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('checkout')]
class OrderConverterController extends AbstractController
{
    /**
     * @internal
     *
     * @param EntityRepository<OrderCollection> $orderRepository
     */
    public function __construct(
        private readonly OrderConverter $orderConverter,
        private readonly AbstractCartPersister $cartPersister,
        private readonly EntityRepository $orderRepository
    ) {
    }

    #[Route(path: '/api/_action/order/{orderId}/convert-to-cart/', name: 'api.action.order.convert-to-cart', methods: ['POST'])]
    public function convertToCart(string $orderId, Context $context): JsonResponse
    {
        $criteria = (new Criteria([$orderId]))
            ->addAssociation('lineItems')
            ->addAssociation('transactions.stateMachineState')
            ->addAssociation('deliveries.positions.orderLineItem');

        $order = $this->orderRepository->search($criteria, $context)->getEntities()->first();
        if (!$order) {
            throw CartException::orderNotFound($orderId);
        }

        $convertedCart = $this->orderConverter->convertToCart($order, $context);

        $this->cartPersister->save(
            $convertedCart,
            $this->orderConverter->assembleChannelContext($order, $context)
        );

        return new JsonResponse(['token' => $convertedCart->getToken()]);
    }
}

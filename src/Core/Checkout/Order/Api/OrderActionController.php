<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Api;

use HeyFrame\Core\Checkout\Order\Channel\OrderService;
use HeyFrame\Core\Checkout\Payment\Cart\PaymentRefundProcessor;
use HeyFrame\Core\Checkout\Payment\PaymentException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('checkout')]
class OrderActionController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentRefundProcessor $paymentRefundProcessor
    ) {
    }

    #[Route(path: '/api/_action/order/{orderId}/state/{transition}', name: 'api.action.order.state_machine.order.transition_state', methods: ['POST'])]
    public function orderStateTransition(
        string $orderId,
        string $transition,
        Request $request,
        Context $context
    ): JsonResponse {
        $toPlace = $this->orderService->orderStateTransition(
            $orderId,
            $transition,
            $request->request,
            $context
        );

        return new JsonResponse($toPlace->jsonSerialize());
    }

    #[Route(path: '/api/_action/order_transaction/{orderTransactionId}/state/{transition}', name: 'api.action.order.state_machine.order_transaction.transition_state', methods: ['POST'])]
    public function orderTransactionStateTransition(
        string $orderTransactionId,
        string $transition,
        Request $request,
        Context $context
    ): JsonResponse {
        $toPlace = $this->orderService->orderTransactionStateTransition(
            $orderTransactionId,
            $transition,
            $request->request,
            $context
        );

        return new JsonResponse($toPlace->jsonSerialize());
    }

    #[Route(path: '/api/_action/order_delivery/{orderDeliveryId}/state/{transition}', name: 'api.action.order.state_machine.order_delivery.transition_state', methods: ['POST'])]
    public function orderDeliveryStateTransition(
        string $orderDeliveryId,
        string $transition,
        Request $request,
        Context $context
    ): JsonResponse {
        $toPlace = $this->orderService->orderDeliveryStateTransition(
            $orderDeliveryId,
            $transition,
            $request->request,
            $context
        );

        return new JsonResponse($toPlace->jsonSerialize());
    }

    /**
     * @throws PaymentException
     */
    #[Route(path: '/api/_action/order_transaction_capture_refund/{refundId}', name: 'api.action.order.order_transaction_capture_refund', defaults: ['_acl' => ['order_refund.editor']], methods: ['POST'])]
    public function refundOrderTransactionCapture(string $refundId, Context $context): JsonResponse
    {
        $this->paymentRefundProcessor->processRefund($refundId, $context);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

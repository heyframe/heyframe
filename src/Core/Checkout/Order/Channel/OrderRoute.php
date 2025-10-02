<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Channel;

use HeyFrame\Core\Checkout\Cart\Rule\PaymentMethodRule;
use HeyFrame\Core\Checkout\Order\Event\OrderCriteriaEvent;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Checkout\Order\OrderException;
use HeyFrame\Core\Framework\Adapter\Database\ReplicaConnection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\RateLimiter\Exception\RateLimitExceededException;
use HeyFrame\Core\Framework\RateLimiter\RateLimiter;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\Framework\Rule\Container\Container;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('checkout')]
class OrderRoute extends AbstractOrderRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<OrderCollection> $orderRepository
     */
    public function __construct(
        private readonly EntityRepository $orderRepository,
        private readonly RateLimiter $rateLimiter,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getDecorated(): AbstractOrderRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/order', name: 'front-api.order', defaults: ['_entity' => 'order'], methods: ['GET', 'POST'])]
    public function load(Request $request, ChannelContext $context, Criteria $criteria): OrderRouteResponse
    {
        ReplicaConnection::ensurePrimary();

        $criteria->addFilter(new EqualsFilter('order.channelId', $context->getChannelId()));

        $criteria->getAssociation('documents')
            ->addFilter(new EqualsFilter('config.displayInCustomerAccount', 'true'))
            ->addFilter(new EqualsFilter('sent', true));

        $criteria->addAssociations(['billingAddress', 'orderCustomer.customer', 'primaryOrderDelivery']);

        $deepLinkFilter = \current(array_filter($criteria->getFilters(), static fn (Filter $filter) => \in_array('order.deepLinkCode', $filter->getFields(), true)
            || \in_array('deepLinkCode', $filter->getFields(), true))) ?: null;

        if ($context->getCustomer()) {
            $criteria->addFilter(new EqualsFilter('order.orderCustomer.customerId', $context->getCustomerId()));
        } elseif ($deepLinkFilter === null) {
            throw OrderException::customerNotLoggedIn();
        }

        $this->eventDispatcher->dispatch(new OrderCriteriaEvent($criteria, $context));

        $orderResult = $this->orderRepository->search($criteria, $context->getContext());
        $orders = $orderResult->getEntities();

        // remove old orders only if there is a deeplink filter
        if ($deepLinkFilter !== null) {
            $orders = $this->filterOldOrders($orders);
        }

        // Handle guest authentication if deeplink is set
        if (!$context->getCustomer() && $deepLinkFilter instanceof EqualsFilter) {
            try {
                $cacheKey = strtolower((string) $deepLinkFilter->getValue()) . '-' . $request->getClientIp();

                $this->rateLimiter->ensureAccepted(RateLimiter::GUEST_LOGIN, $cacheKey);
            } catch (RateLimitExceededException $exception) {
                throw OrderException::customerAuthThrottledException($exception->getWaitTime(), $exception);
            }
        }

        if (isset($cacheKey)) {
            $this->rateLimiter->reset(RateLimiter::GUEST_LOGIN, $cacheKey);
        }

        return new OrderRouteResponse($orderResult);
    }

    private function checkRuleType(Container $rule): bool
    {
        foreach ($rule->getRules() as $nestedRule) {
            if ($nestedRule instanceof Container && $this->checkRuleType($nestedRule) === false) {
                return false;
            }
            if ($nestedRule instanceof PaymentMethodRule) {
                return false;
            }
        }

        return true;
    }

    private function filterOldOrders(OrderCollection $orders): OrderCollection
    {
        // Search with deepLinkCode needs updatedAt Filter
        $latestOrderDate = (new \DateTime())->setTimezone(new \DateTimeZone('Asia/Shanghai'))->modify(-abs(30) . ' Day');

        return $orders->filter(fn (OrderEntity $order) => $order->getCreatedAt() > $latestOrderDate || $order->getUpdatedAt() > $latestOrderDate);
    }
}

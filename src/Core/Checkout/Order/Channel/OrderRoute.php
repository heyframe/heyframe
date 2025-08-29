<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Channel;

use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\Exception\CustomerNotLoggedInException;
use HeyFrame\Core\Checkout\Cart\Rule\PaymentMethodRule;
use HeyFrame\Core\Checkout\Order\Event\OrderCriteriaEvent;
use HeyFrame\Core\Checkout\Order\Exception\WrongGuestCredentialsException;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Checkout\Order\OrderEntity;
use HeyFrame\Core\Checkout\Order\OrderException;
use HeyFrame\Core\Checkout\Promotion\PromotionCollection;
use HeyFrame\Core\Checkout\Promotion\PromotionEntity;
use HeyFrame\Core\Content\Rule\RuleEntity;
use HeyFrame\Core\Framework\Adapter\Database\ReplicaConnection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use HeyFrame\Core\Framework\Feature;
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
     * @param EntityRepository<PromotionCollection> $promotionRepository
     */
    public function __construct(
        private readonly EntityRepository $orderRepository,
        private readonly EntityRepository $promotionRepository,
        private readonly RateLimiter $rateLimiter,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getDecorated(): AbstractOrderRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/order', name: 'store-api.order', methods: ['GET', 'POST'], defaults: ['_entity' => 'order'])]
    public function load(Request $request, ChannelContext $context, Criteria $criteria): OrderRouteResponse
    {
        ReplicaConnection::ensurePrimary();

        $criteria->addFilter(new EqualsFilter('order.channelId', $context->getChannelId()));

        $criteria->getAssociation('documents')
            ->addFilter(new EqualsFilter('config.displayInCustomerAccount', 'true'))
            ->addFilter(new EqualsFilter('sent', true));

        $criteria->addAssociations(['billingAddress', 'orderCustomer.customer', 'primaryOrderDelivery']);

        if (!Feature::isActive('v6.8.0.0')) {
            $criteria->addAssociation('deliveries');
        }

        $deepLinkFilter = \current(array_filter($criteria->getFilters(), static fn (Filter $filter) => \in_array('order.deepLinkCode', $filter->getFields(), true)
            || \in_array('deepLinkCode', $filter->getFields(), true))) ?: null;

        if ($context->getCustomer()) {
            $criteria->addFilter(new EqualsFilter('order.orderCustomer.customerId', $context->getCustomerId()));
        } elseif ($deepLinkFilter === null) {
            // @deprecated tag:v6.8.0 - remove this if block
            if (!Feature::isActive('v6.8.0.0')) {
                throw CartException::customerNotLoggedIn(); // @phpstan-ignore heyframe.domainException
            }
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

            $order = $orders->first();
            $this->checkGuestAuth($order, $request);
        }

        if (isset($cacheKey)) {
            $this->rateLimiter->reset(RateLimiter::GUEST_LOGIN, $cacheKey);
        }

        $response = new OrderRouteResponse($orderResult);
        if ($request->get('checkPromotion') === true) {
            foreach ($orders as $order) {
                $promotions = $this->getActivePromotions($order, $context);
                $changeable = true;
                foreach ($promotions as $promotion) {
                    $changeable = $this->checkPromotion($promotion);
                    if ($changeable === true) {
                        break;
                    }
                }
                $response->addPaymentChangeable([$order->getId() => $changeable]);
            }
        }

        return $response;
    }

    private function getActivePromotions(OrderEntity $order, ChannelContext $context): PromotionCollection
    {
        $promotionIds = [];
        foreach ($order->getLineItems() ?? [] as $lineItem) {
            $payload = $lineItem->getPayload();
            if (isset($payload['promotionId']) && \is_string($payload['promotionId'])) {
                $promotionIds[] = $payload['promotionId'];
            }
        }

        if (!$promotionIds) {
            return new PromotionCollection();
        }

        $criteria = (new Criteria($promotionIds))
            ->addAssociation('cartRules');

        return $this->promotionRepository->search($criteria, $context->getContext())->getEntities();
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

    private function checkPromotion(PromotionEntity $promotion): bool
    {
        if ($promotion->getCartRules() === null) {
            return true;
        }

        foreach ($promotion->getCartRules() as $cartRule) {
            if (!$this->checkCartRule($cartRule)) {
                return false;
            }
        }

        return true;
    }

    private function checkCartRule(RuleEntity $cartRule): bool
    {
        $payload = $cartRule->getPayload();
        if (!$payload instanceof Container) {
            return true;
        }

        foreach ($payload->getRules() as $rule) {
            if ($rule instanceof Container && $this->checkRuleType($rule) === false) {
                return false;
            }
        }

        return true;
    }

    private function filterOldOrders(OrderCollection $orders): OrderCollection
    {
        // Search with deepLinkCode needs updatedAt Filter
        $latestOrderDate = (new \DateTime())->setTimezone(new \DateTimeZone('UTC'))->modify(-abs(30) . ' Day');

        return $orders->filter(fn (OrderEntity $order) => $order->getCreatedAt() > $latestOrderDate || $order->getUpdatedAt() > $latestOrderDate);
    }

    /**
     * @throws CustomerNotLoggedInException
     * @throws WrongGuestCredentialsException
     */
    private function checkGuestAuth(?OrderEntity $order, Request $request): void
    {
        if ($order === null) {
            throw OrderException::guestNotAuthenticated();
        }

        $orderCustomer = $order->getOrderCustomer();
        if ($orderCustomer === null) {
            // @deprecated tag:v6.8.0 - remove this if block
            if (!Feature::isActive('v6.8.0.0')) {
                throw CartException::customerNotLoggedIn(); // @phpstan-ignore heyframe.domainException
            }
            throw OrderException::customerNotLoggedIn();
        }

        $guest = $orderCustomer->getCustomer() !== null && $orderCustomer->getCustomer()->getGuest();
        // Throw exception when customer is not guest
        if (!$guest) {
            // @deprecated tag:v6.8.0 - remove this if block
            if (!Feature::isActive('v6.8.0.0')) {
                throw CartException::customerNotLoggedIn(); // @phpstan-ignore heyframe.domainException
            }
            throw OrderException::customerNotLoggedIn();
        }

        // Verify email and zip code with this order
        if ($request->get('email', false) && $request->get('zipcode', false)) {
            $zipCode = $order->getBillingAddress()?->getZipcode();
            if ($zipCode === null
                || strtolower($request->get('email')) !== strtolower($orderCustomer->getEmail())
                || strtoupper($request->get('zipcode')) !== strtoupper($zipCode)) {
                throw OrderException::wrongGuestCredentials();
            }
        } else {
            throw OrderException::guestNotAuthenticated();
        }
    }
}

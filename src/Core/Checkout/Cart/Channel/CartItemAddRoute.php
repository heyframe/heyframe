<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Channel;

use HeyFrame\Core\Checkout\Cart\AbstractCartPersister;
use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartCalculator;
use HeyFrame\Core\Checkout\Cart\CartLocker;
use HeyFrame\Core\Checkout\Cart\Event\AfterLineItemAddedEvent;
use HeyFrame\Core\Checkout\Cart\Event\BeforeLineItemAddedEvent;
use HeyFrame\Core\Checkout\Cart\Event\CartChangedEvent;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItemFactoryRegistry;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\RateLimiter\RateLimiter;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('checkout')]
class CartItemAddRoute extends AbstractCartItemAddRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly CartCalculator $cartCalculator,
        private readonly AbstractCartPersister $cartPersister,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LineItemFactoryRegistry $lineItemFactory,
        private readonly RateLimiter $rateLimiter,
        private readonly CartLocker $cartLocker
    ) {
    }

    public function getDecorated(): AbstractCartItemAddRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @param array<LineItem>|null $items
     */
    #[Route(path: '/store-api/checkout/cart/line-item', name: 'store-api.checkout.cart.add', methods: ['POST'])]
    public function add(Request $request, Cart $cart, ChannelContext $context, ?array $items): CartResponse
    {
        return $this->cartLocker->locked($context, function () use ($request, $cart, $context, $items) {
            if ($items === null) {
                $items = [];

                /** @var array<mixed> $item */
                foreach ($request->request->all('items') as $item) {
                    $items[] = $this->lineItemFactory->create($item, $context);
                }
            }

            foreach ($items as $item) {
                if ($request->getClientIp() !== null) {
                    $cacheKey = ($item->getReferencedId() ?? $item->getId()) . '-' . $request->getClientIp();
                    $this->rateLimiter->ensureAccepted(RateLimiter::CART_ADD_LINE_ITEM, $cacheKey);
                }

                $alreadyExists = $cart->has($item->getId());
                $cart->add($item);

                $this->eventDispatcher->dispatch(new BeforeLineItemAddedEvent($item, $cart, $context, $alreadyExists));
            }

            $cart->markModified();

            $cart = $this->cartCalculator->calculate($cart, $context);
            $this->cartPersister->save($cart, $context);

            $this->eventDispatcher->dispatch(new AfterLineItemAddedEvent($items, $cart, $context));
            $this->eventDispatcher->dispatch(new CartChangedEvent($cart, $context));

            return new CartResponse($cart);
        });
    }
}

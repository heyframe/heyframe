<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Channel;

use HeyFrame\Core\Checkout\Cart\AbstractCartPersister;
use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartCalculator;
use HeyFrame\Core\Checkout\Cart\CartException;
use HeyFrame\Core\Checkout\Cart\CartLocker;
use HeyFrame\Core\Checkout\Cart\Event\AfterLineItemRemovedEvent;
use HeyFrame\Core\Checkout\Cart\Event\BeforeLineItemRemovedEvent;
use HeyFrame\Core\Checkout\Cart\Event\CartChangedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('checkout')]
class CartItemRemoveRoute extends AbstractCartItemRemoveRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly CartCalculator $cartCalculator,
        private readonly AbstractCartPersister $cartPersister,
        private readonly CartLocker $cartLocker
    ) {
    }

    public function getDecorated(): AbstractCartItemRemoveRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/checkout/cart/line-item', name: 'front-api.checkout.cart.remove-item', methods: ['DELETE'])]
    #[Route(path: '/front-api/checkout/cart/line-item/delete', name: 'front-api.checkout.cart.remove-item-v2', methods: ['POST'])]
    public function remove(Request $request, Cart $cart, ChannelContext $context): CartResponse
    {
        return $this->cartLocker->locked($context, function () use ($request, $cart, $context) {
            $ids = $request->get('ids');
            $lineItems = [];

            foreach ($ids as $id) {
                if (!\is_string($id)) {
                    throw CartException::lineItemNotFound((string) $id);
                }

                $lineItem = $cart->get($id);

                if (!$lineItem) {
                    throw CartException::lineItemNotFound($id);
                }
                $lineItems[] = $lineItem;

                $cart->remove($id);

                $this->eventDispatcher->dispatch(new BeforeLineItemRemovedEvent($lineItem, $cart, $context));

                $cart->markModified();
            }

            $cart = $this->cartCalculator->calculate($cart, $context);
            $this->cartPersister->save($cart, $context);

            $this->eventDispatcher->dispatch(new AfterLineItemRemovedEvent($lineItems, $cart, $context));
            $this->eventDispatcher->dispatch(new CartChangedEvent($cart, $context));

            return new CartResponse($cart);
        });
    }
}

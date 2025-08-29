<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Channel;

use HeyFrame\Core\Checkout\Cart\AbstractCartPersister;
use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\CartCalculator;
use HeyFrame\Core\Checkout\Cart\CartLocker;
use HeyFrame\Core\Checkout\Cart\Event\AfterLineItemQuantityChangedEvent;
use HeyFrame\Core\Checkout\Cart\Event\CartChangedEvent;
use HeyFrame\Core\Checkout\Cart\LineItemFactoryRegistry;
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
class CartItemUpdateRoute extends AbstractCartItemUpdateRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractCartPersister $cartPersister,
        private readonly CartCalculator $cartCalculator,
        private readonly LineItemFactoryRegistry $lineItemFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly CartLocker $cartLocker
    ) {
    }

    public function getDecorated(): AbstractCartItemUpdateRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/checkout/cart/line-item', name: 'store-api.checkout.cart.update-lineitem', methods: ['PATCH'])]
    public function change(Request $request, Cart $cart, ChannelContext $context): CartResponse
    {
        return $this->cartLocker->locked($context, function () use ($request, $cart, $context) {
            $itemsToUpdate = $request->request->all('items');

            /** @var array<mixed> $item */
            foreach ($itemsToUpdate as $item) {
                $this->lineItemFactory->update($cart, $item, $context);
            }

            $cart->markModified();

            $cart = $this->cartCalculator->calculate($cart, $context);
            $this->cartPersister->save($cart, $context);

            $this->eventDispatcher->dispatch(new AfterLineItemQuantityChangedEvent($cart, $itemsToUpdate, $context));
            $this->eventDispatcher->dispatch(new CartChangedEvent($cart, $context));

            return new CartResponse($cart);
        });
    }
}

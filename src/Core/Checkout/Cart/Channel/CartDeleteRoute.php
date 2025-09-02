<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Channel;

use HeyFrame\Core\Checkout\Cart\AbstractCartPersister;
use HeyFrame\Core\Checkout\Cart\CartLocker;
use HeyFrame\Core\Checkout\Cart\Event\CartDeletedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\NoContentResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('checkout')]
class CartDeleteRoute extends AbstractCartDeleteRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractCartPersister $cartPersister,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly CartLocker $cartLocker
    ) {
    }

    public function getDecorated(): AbstractCartDeleteRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/checkout/cart', name: 'front-api.checkout.cart.delete', methods: ['DELETE'])]
    public function delete(ChannelContext $context): NoContentResponse
    {
        return $this->cartLocker->locked($context, function () use ($context) {
            $this->cartPersister->delete($context->getToken(), $context);

            $cartDeleteEvent = new CartDeletedEvent($context);
            $this->eventDispatcher->dispatch($cartDeleteEvent);

            return new NoContentResponse();
        });
    }
}
